<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Session;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingCharge;
use App\Models\DiscountCoupon;
use Illuminate\Support\Carbon;

class CartController extends Controller
{
    public function addToCart(Request $request) {
        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }

        if (Cart::count() > 0) {
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, [
                    'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : ''
                ]);
                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
                Session::flash('success', $message);
            } else {
                $status = false;
                $message = $product->title.' already added in cart';
            }

        } else {
            Cart::add($product->id, $product->title, 1, $product->price, [
                'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : ''
            ]);
            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
            Session::flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function cart() {
        $cartContent = Cart::content();
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }

    public function updateCart(Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);

        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                Session::flash('success', $message);
            } else {
                $message = 'Requested quantity ('.$qty.') not available in stock.';
                $status = false;
                Session::flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            Session::flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request) {
        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null) {
            Session::flash('error', 'Item not found in cart');
            return response()->json([
                'status' => false,
                'message' => 'Item not found in cart'
            ]);
        }

        Cart::remove($request->rowId);
        Session::flash('success', 'Item removed from cart successfully');

        return response()->json([
            'status' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    public function checkout() {
        if (Cart::count() == 0) {
            return redirect()->route('front.cart');
        }

        if (Auth::check() == false) {
            if (!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            return redirect()->route('account.login');
        }

        session()->forget('url.intended');

        $countries = Country::orderBy('name', 'ASC')->get();
        $customerAddress = CustomerAddress::where('user_id', Auth::user()->id)->first();

        $totalQty = 0;
        $totalShippingCharge = 0;
        $discount = 0;

        $subTotalString = Cart::subtotal(2, '.', '');
        $subTotal = (float) str_replace(',', '', $subTotalString);

        if (session()->has('code')) {
            $code = session()->get('code');

            if ($code->min_amount > 0) {
                if ($subTotal < $code->min_amount) {
                    session()->forget('code');
                }
            }
        }

        foreach (Cart::content() as $item) {
            $totalQty += $item->qty;
        }

        if (session()->has('code')) {
            $code = session()->get('code');

            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }

        if ($customerAddress != null) {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

            if ($shippingInfo != null) {
                $totalShippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $totalShippingCharge;
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                if ($shippingInfo != null) {
                    $totalShippingCharge = $totalQty * $shippingInfo->amount;
                } else {
                    $totalShippingCharge = 0;
                }
                $grandTotal = ($subTotal - $discount) + $totalShippingCharge;
            }
        } else {
            $grandTotal = ($subTotal - $discount);
            $totalShippingCharge = 0;
        }

        return view('front.checkout', [
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'discount' => $discount,
            'grandTotal' => $grandTotal
        ]);
    }

    public function getOrderSummary(Request $request) {
        // YAHAN FIX KIYA HAI: Ab koi aadhi line missing nahi hai
        $subTotalString = Cart::subtotal(2, '.', '');
        $subTotal = (float) str_replace(',', '', $subTotalString);
        $discount = 0;
        $discountString = '';

        if (session()->has('code')) {
            $code = session()->get('code');

            if ($code->type == 'percent') {
                $discount = ($code->discount_amount / 100) * $subTotal;
            } else {
                $discount = $code->discount_amount;
            }

            $discountString = '<div class="mt-4" id="discount-response">
                                <strong>'.session()->get('code')->code.'</strong>
                                <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
                               </div>';
        }

        $totalQty = 0;
        foreach (Cart::content() as $item) {
            $totalQty += $item->qty;
        }

        if ($request->country_id > 0) {
            $shippingInfo = ShippingCharge::where('country_id', $request->country_id)->first();

            if ($shippingInfo != null) {
                $shippingCharge = $totalQty * $shippingInfo->amount;
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2, '.', ''),
                    'discount' => number_format($discount, 2, '.', ''),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge, 2, '.', ''),
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                if ($shippingInfo != null) {
                    $shippingCharge = $totalQty * $shippingInfo->amount;
                } else {
                    $shippingCharge = 0;
                }
                $grandTotal = ($subTotal - $discount) + $shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal, 2, '.', ''),
                    'discount' => number_format($discount, 2, '.', ''),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge, 2, '.', ''),
                ]);
            }
        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal - $discount), 2, '.', ''),
                'discount' => number_format($discount, 2, '.', ''),
                'discountString' => $discountString,
                'shippingCharge' => number_format(0, 2, '.', ''),
            ]);
        }
    }

    public function applyDiscount(Request $request) {
        $code = DiscountCoupon::where('code', $request->code)->first();

        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon'
            ]);
        }

        $now = Carbon::now();

        if (!empty($code->starts_at)) {
            $startDate = Carbon::parse($code->starts_at);
            if ($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon (Not started yet)'
                ]);
            }
        }

        if (!empty($code->expires_at)) {
            $endDate = Carbon::parse($code->expires_at);
            if ($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon (Expired)'
                ]);
            }
        }

        if ($code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id', $code->id)->count();
            if ($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon (Max uses reached)'
                ]);
            }
        }

        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();
            if ($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already used this coupon.'
                ]);
            }
        }

        $subTotalString = Cart::subtotal(2, '.', '');
        $subTotal = (float) str_replace(',', '', $subTotalString);

        if ($code->min_amount > 0) {
            if ($subTotal < $code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your min order amount must be $'.$code->min_amount.'.'
                ]);
            }
        }

        session()->put('code', $code);
        return $this->getOrderSummary($request);
    }

    // YAHAN FIX KIYA HAI: Ye removeCoupon aapke code mein missing tha!
    public function removeCoupon(Request $request) {
        session()->forget('code');
        return $this->getOrderSummary($request);
    }
public function processCheckout(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:10',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required|min:10'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please fix the errors', 'status' => false, 'errors' => $validator->errors()]);
        }

        try {
            $user = Auth::user();
            CustomerAddress::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'country_id' => $request->country,
                    'address' => $request->address,
                    'apartment' => $request->apartment,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip
                ]
            );

            if ($request->payment_method == 'cod') {
                $discountCodeId = NULL;
                $promoCode = '';
                $shipping = 0;
                $discount = 0;

                $subTotal = (float) str_replace(',', '', Cart::subtotal(2, '.', ''));

                if (session()->has('code')) {
                    $code = session()->get('code');
                    $discount = ($code->type == 'percent') ? ($code->discount_amount / 100) * $subTotal : $code->discount_amount;
                    $discountCodeId = $code->id;
                    $promoCode = $code->code;
                }

                $totalQty = 0;
                foreach (Cart::content() as $item) { $totalQty += $item->qty; }

                $shippingInfo = ShippingCharge::where('country_id', $request->country)->first();
                if ($shippingInfo == null) {
                    $shippingInfo = ShippingCharge::where('country_id', 'rest_of_world')->first();
                }
                $shipping = ($shippingInfo != null) ? $totalQty * $shippingInfo->amount : 0;

                $grandTotal = ($subTotal - $discount) + $shipping;

                $order = new Order;
                $order->subtotal = $subTotal;
                $order->shipping = $shipping;
                $order->grand_total = $grandTotal;
                $order->discount = $discount;
                $order->coupon_code = $promoCode;
                $order->coupon_code_id = $discountCodeId;
                $order->user_id = $user->id;
                $order->status = 'pending';
                $order->first_name = $request->first_name;
                $order->last_name = $request->last_name;
                $order->email = $request->email;
                $order->mobile = $request->mobile;
                $order->address = $request->address;
                $order->apartment = $request->apartment;
                $order->state = $request->state;
                $order->city = $request->city;
                $order->zip = $request->zip;
                $order->notes = $request->order_notes;
                $order->country_id = $request->country;
                $order->save();

                // YAHAN FIX KIYA HAI: Ek hi loop mein Item Save + Stock Update
                foreach (Cart::content() as $item) {
                    // 1. Order Item Save
                    $orderItem = new OrderItem;
                    $orderItem->product_id = $item->id;
                    $orderItem->order_id = $order->id;
                    $orderItem->name = $item->name;
                    $orderItem->qty = $item->qty;
                    $orderItem->price = $item->price;
                    $orderItem->total = $item->price * $item->qty;
                    $orderItem->save();

                    // 2. STOCK UPDATE (Video #43 exact logic)
                    $productData = Product::find($item->id);
                    if ($productData->track_qty == 'Yes') {
                        $productData->qty = $productData->qty - $item->qty;
                        $productData->save();
                    }
                }

                Cart::destroy();
                session()->forget('code');
                orderEmail($order->id, 'customer');

                session()->flash('success', 'You have successfully placed your order.');
                return response()->json(['message' => 'Order saved successfully', 'orderId' => $order->id, 'status' => true]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Backend Error: ' . $e->getMessage()]);
        }
    }
    public function thankYou($id) {
        return view('front.thanks', [
            'id' => $id
        ]);
    }
}

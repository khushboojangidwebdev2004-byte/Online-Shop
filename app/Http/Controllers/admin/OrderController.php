<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;

use Illuminate\Http\Request;


class OrderController extends Controller
{
    public function index(Request $request) {
        $orders = Order::latest('created_at')->select('orders.*', 'users.name', 'users.email');
        $orders = $orders->leftJoin('users', 'users.id', 'orders.user_id');

        if ($request->get('keyword') != "") {
            $orders = $orders->where('users.name', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('users.email', 'like', '%' . $request->keyword . '%');
            $orders = $orders->orWhere('orders.id', 'like', '%' . $request->keyword . '%');
        }

        $orders = $orders->paginate(10);

        return view('admin.orders.list', [
            'orders' => $orders
        ]);
    }

    public function detail($orderId) {
        // Order ke saath country ka naam laane ke liye Join kiya gaya hai
        $order = Order::select('orders.*', 'countries.name as countryName')
                    ->where('orders.id', $orderId)
                    ->leftJoin('countries', 'countries.id', 'orders.country_id')
                    ->first();

        if (empty($order)) {
            session()->flash('error', 'Order not found');
            return redirect()->route('orders.index');
        }

        $orderItems = OrderItem::where('order_id', $orderId)->get();

        return view('admin.orders.detail', [
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }
 public function changeOrderStatus(Request $request, $orderId) {
        $order = Order::find($orderId);
        $order->status = $request->status;

        // YAHAN FIX KIYA HAI: Admin input se aayi hui shipped date ko save kar rahe hain
        $order->shipped_date = $request->shipped_date;

        $order->save();

        session()->flash('success', 'Order status updated successfully');

        return response()->json([
            'status' => true,
            'message' => 'Order status updated successfully'
        ]);
    }

 // YAHAN NAYA FUNCTION ADD KIYA HAI SEND EMAIL KE LIYE
    public function sendInvoiceEmail(Request $request, $orderId) {
        orderEmail($orderId, $request->user_type);

        session()->flash('success', 'Order email sent successfully');

        return response()->json([
            'status' => true,
            'message' => 'Order email sent successfully'
        ]);
    }
}

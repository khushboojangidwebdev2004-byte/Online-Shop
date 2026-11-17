<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\Page;
use App\Mail\ContactEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class FrontController extends Controller
{
    public function index() {

        // Featured products fetch karna
        $featuredProducts = Product::where('is_featured', 'Yes')
                                ->orderBy('id', 'DESC')
                                ->where('status', 1)
                                ->take(8)
                                ->get();

        // Latest products fetch karna
        $latestProducts = Product::orderBy('id', 'DESC')
                                ->where('status', 1)
                                ->take(8)
                                ->get();


        $data['featuredProducts'] = $featuredProducts;
        $data['latestProducts'] = $latestProducts;

        return view('front.home', $data);
    }
    public function addToWishlist(Request $request) {
        if (Auth::check() == false) {
            session(['url.intended' => url()->previous()]);
            return response()->json([
                'status' => false
            ]);
        }

        $product = Product::where('id', $request->id)->first();

        if ($product == null) {
            return response()->json([
                'status' => true,
                'message' => '<div class="alert alert-danger">Product not found.</div>'
            ]);
        }

        Wishlist::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id,
            ],
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => '<div class="alert alert-success"><strong>"'.$product->title.'"</strong> added in your wishlist</div>'
        ]);
    }

    public function wishlist() {
        $wishlists = Wishlist::where('user_id', Auth::user()->id)->with('product')->get();
        $data['wishlists'] = $wishlists;
        return view('front.account.wishlist', $data);
    }

    public function removeProductFromWishlist(Request $request) {
        $wishlist = Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->first();

        if ($wishlist == null) {
            session()->flash('error', 'Product already removed');
            return response()->json([
                'status' => true
            ]);
        } else {
            Wishlist::where('user_id', Auth::user()->id)->where('product_id', $request->id)->delete();
            session()->flash('success', 'Product removed successfully');
            return response()->json([
                'status' => true
            ]);
        }
    }
    // PRODUCT DETAIL PAGE FUNCTION
    public function product($slug) {
        $product = Product::where('slug', $slug)
                    ->with('product_images')
                    ->withCount('product_ratings') // Reviews ka count nikalna
                ->withSum('product_ratings', 'rating') // Rating column ka sum nikalna
                ->with(['product_images', 'product_ratings']) // Relation ko load karna
                ->first();

        if ($product == null) {
            abort(404);
        }

        // Average Rating Calculation (Video exact code)
    $avgRating = '0.00';
    $avgRatingPer = 0;

    if ($product->product_ratings_count > 0) {
        $avgRating = number_format(($product->product_ratings_sum_rating / $product->product_ratings_count), 2);
        $avgRatingPer = ($avgRating * 100) / 5;
    }
        // YAHAN AAYEGA RELATED PRODUCTS KA CODE (Video #43 fix)
        $relatedProducts = Product::where('status', 1)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id) // Khud ussi product ko mat dikhao
            ->take(4)
            ->get();

       return view('front.product', [
        'product' => $product,
        'relatedProducts' => $relatedProducts,
        'avgRating' => $avgRating,
        'avgRatingPer' => $avgRatingPer
    ]);
    }
    public function page($slug) {
    $page = Page::where('slug', $slug)->first();

    // Video wala logic: Agar page nahi milta toh 404 error dikhao
    if ($page == null) {
        abort(404);
    }

    return view('front.page', [
        'page' => $page
    ]);
}
// Method add karein
public function sendContactEmail(Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email',
        'subject' => 'required|min:10',
    ]);

    if ($validator->passes()) {
        // Admin ka email nikalna (ID 1 se)
        $admin = User::where('id', 1)->first();

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'mail_subject' => 'You have received a contact email'
        ];

        Mail::to($admin->email)->send(new ContactEmail($mailData));

        session()->flash('success', 'Thanks for contacting us, we will get back to you soon.');

        return response()->json([
            'status' => true
        ]);

    } else {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}
}

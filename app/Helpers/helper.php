<?php
use App\Models\Category;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderEmail;
use App\Models\ProductImage;
use App\Models\Page;
function getCategories() {
    return Category::orderBy('name', 'ASC')
            ->with('sub_category')
            ->orderBy('id','DESC')
            ->where('status', 1)
            ->where('showHome', 'Yes')
            ->get();
}
// YAHAN FIX KIYA HAI: Crash hone wali line theek kar di hai
function orderEmail($orderId, $userType = "customer") {
    // with('country') hata diya gaya hai jo error de raha tha
    $order = Order::where('id', $orderId)->first();
    $orderItems = OrderItem::where('order_id', $orderId)->get();

    if ($userType == 'customer') {
        $subject = 'Thanks for your order';
        $email = $order->email;
    } else {
        $subject = 'You have received an order';
        $email = env('ADMIN_EMAIL', 'admin@example.com'); // Default admin email de diya hai
    }

    $mailData = [
        'subject' => $subject,
        'order' => $order,
        'orderItems' => $orderItems,
        'userType' => $userType
    ];

    // Email bhejne ka command
    Mail::to($email)->send(new OrderEmail($mailData));
}
function getProductImage($productId) {
    return ProductImage::where('product_id', $productId)->first();
}
function staticPages() {
    return Page::orderBy('name', 'ASC')->get();
}

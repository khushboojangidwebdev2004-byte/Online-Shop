<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function index() {
        // 1. Total Orders (excluding cancelled)
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();

        // 2. Total Products
        $totalProducts = Product::count();

        // 3. Total Customers (Role 1 assumes customers)
        $totalCustomers = User::where('role', 1)->count();

        // 4. Total Revenue
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('grand_total');

        // 5. Revenue This Month
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');

        $revenueThisMonth = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $startOfMonth)
                            ->whereDate('created_at', '<=', $currentDate)
                            ->sum('grand_total');

        // 6. Revenue Last Month
        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonthName = Carbon::now()->subMonth()->format('M');

        $revenueLastMonth = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $lastMonthStartDate)
                            ->whereDate('created_at', '<=', $lastMonthEndDate)
                            ->sum('grand_total');

        // 7. Revenue Last 30 Days
        $last30DayStartDate = Carbon::now()->subDays(30)->format('Y-m-d');

        $revenueLast30Days = Order::where('status', '!=', 'cancelled')
                            ->whereDate('created_at', '>=', $last30DayStartDate)
                            ->whereDate('created_at', '<=', $currentDate)
                            ->sum('grand_total');

        // ==========================================
        // VIDEO #59: DELETE TEMP / GARBAGE IMAGES HERE
        // ==========================================

        // Aaj ki date se 1 din pehle ka date & time nikalna
        $dayBeforeToday = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');

        // Database se purani temporary images fetch karna
        $tempImages = TempImage::where('created_at', '<=', $dayBeforeToday)->get();

        foreach ($tempImages as $tempImage) {

            // Image ka path public folder se nikalna
            $path = public_path('/temp/'.$tempImage->name);
            $thumbPath = public_path('/temp/thumb/'.$tempImage->name);

            // Delete Main Image
            if (File::exists($path)) {
                File::delete($path);
            }

            // Delete Thumb Image
            if (File::exists($thumbPath)) {
                File::delete($thumbPath);
            }

            // Delete record from database
            TempImage::where('id', $tempImage->id)->delete();
        }
        return view('admin.dashboard', [
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'revenueLast30Days' => $revenueLast30Days,
            'lastMonthName' => $lastMonthName,
        ]);
    }
}

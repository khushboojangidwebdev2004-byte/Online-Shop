<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerAddress;
use App\Models\Country;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login() {
        return view('front.account.login');
    }

    public function register() {
        return view('front.account.register');
    }

    public function processRegister(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|confirmed'
        ]);

        if ($validator->passes()) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have been registered successfully.');

            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function authenticate(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->passes()) {

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')
                    ->withInput($request->only('email'))
                    ->with('error', 'Either email/password is incorrect.');
            }

        } else {
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }



 public function logout() {
        Auth::logout();

        return redirect()->route('account.login')
            ->with('success', 'You successfully logged out!');
    }
    public function orders() {
        $user = Auth::user();

        // User ke saare orders fetch karna, latest order sabse upar (DESC order mein)
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();

        return view('front.account.order', [
            'orders' => $orders
        ]);
    }

    public function orderDetail($id) {
        $user = Auth::user();

        // Order dhoondhna jo is user ka ho
        $order = Order::where('user_id', $user->id)->where('id', $id)->first();

        // Agar order nahi mila ya kisi aur ka order hai, toh orders page par wapas bhej do
        if ($order == null) {
            return redirect()->route('account.orders');
        }

        // YAHAN FIX KIYA HAI: 'product' aur 'product.product_images' ko with() ke zariye load kiya
        $orderItems = OrderItem::with('product', 'product.product_images')->where('order_id', $order->id)->get();
        $orderItemsCount = OrderItem::where('order_id', $order->id)->count();

        $data['order'] = $order;
        $data['orderItems'] = $orderItems;
        $data['orderItemsCount'] = $orderItemsCount;

        return view('front.account.order-detail', $data);
    }
    // profile function ko update karein
public function profile() {
    $userId = Auth::user()->id;
    $countries = Country::orderBy('name', 'ASC')->get();
    $user = User::where('id', $userId)->first();
    $address = CustomerAddress::where('user_id', $userId)->first();

    return view('front.account.profile', [
        'user' => $user,
        'countries' => $countries,
        'address' => $address
    ]);
}

// Update Profile Function
public function updateProfile(Request $request) {
    $userId = Auth::user()->id;
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users,email,'.$userId.',id',
        'phone' => 'required'
    ]);

    if ($validator->passes()) {
        $user = User::find($userId);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        session()->flash('success', 'Profile updated successfully');

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}

// Update Address Function
public function updateAddress(Request $request) {
    $userId = Auth::user()->id;
    $validator = Validator::make($request->all(), [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email',
        'mobile' => 'required',
        'country_id' => 'required',
        'address' => 'required',
        'city' => 'required',
        'state' => 'required',
        'zip' => 'required'
    ]);

    if ($validator->passes()) {
        CustomerAddress::updateOrCreate(
            ['user_id' => $userId],
            [
                'user_id' => $userId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country_id,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip
            ]
        );

        session()->flash('success', 'Address updated successfully');

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully'
        ]);
    } else {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ]);
    }
}
// Change Password Page dikhane ke liye
public function showChangePasswordForm() {
    return view('front.account.change-password');
}

// Password update karne ka logic
public function changePassword(Request $request) {
    $validator = Validator::make($request->all(), [
        'old_password' => 'required',
        'new_password' => 'required|min:5',
        'confirm_password' => 'required|same:new_password',
    ]);

    if ($validator->passes()) {
        $user = User::select('id', 'password')->find(Auth::user()->id);

        // Purana password check karna
        if (!Hash::check($request->old_password, $user->password)) {
            session()->flash('error', 'Your old password is incorrect, please try again.');
            return response()->json([
                'status' => true
            ]);
        }

        // Naya password save karna
        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        session()->flash('success', 'You have successfully changed your password.');

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
// 1. Forgot Password Page Show
public function forgotPassword() {
    return view('front.account.forgot-password');
}

// 2. Process Forgot Password (Send Email)
public function processForgotPassword(Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email'
    ]);

    if ($validator->fails()) {
        return redirect()->route('front.forgotPassword')->withErrors($validator)->withInput();
    }

    $token = Str::random(60);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    // Send Email
    $user = User::where('email', $request->email)->first();
    $mailData = [
        'token' => $token,
        'user' => $user,
        'subject' => 'Reset your password'
    ];

    Mail::to($request->email)->send(new ResetPasswordEmail($mailData));

    return redirect()->route('front.forgotPassword')->with('success', 'Please check your inbox to reset password.');
}

// 3. Reset Password Page Show
public function resetPassword($token) {
    $tokenExists = DB::table('password_reset_tokens')->where('token', $token)->first();

    if ($tokenExists == null) {
        return redirect()->route('front.forgotPassword')->with('error', 'Invalid request.');
    }

    return view('front.account.reset-password', ['token' => $token]);
}

// 4. Process Reset Password
public function processResetPassword(Request $request) {
    $token = $request->token;
    $tokenExists = DB::table('password_reset_tokens')->where('token', $token)->first();

    if ($tokenExists == null) {
        return redirect()->route('front.forgotPassword')->with('error', 'Invalid request.');
    }

    $validator = Validator::make($request->all(), [
        'new_password' => 'required|min:5',
        'confirm_password' => 'required|same:new_password'
    ]);

    if ($validator->fails()) {
        return redirect()->route('front.resetPassword', $token)->withErrors($validator);
    }

    User::where('email', $tokenExists->email)->update([
        'password' => Hash::make($request->new_password)
    ]);

    DB::table('password_reset_tokens')->where('email', $tokenExists->email)->delete();

    return redirect()->route('account.login')->with('success', 'You have successfully updated your password.');
}
}

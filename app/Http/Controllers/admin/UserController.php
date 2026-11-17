<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request) {
        $users = User::latest();

        if (!empty($request->get('keyword'))) {
            $users = $users->where('name', 'like', '%'.$request->get('keyword').'%');
            $users = $users->orWhere('email', 'like', '%'.$request->get('keyword').'%');
        }

        $users = $users->paginate(10);

        return view('admin.users.list', [
            'users' => $users
        ]);
    }

    public function create() {
        return view('admin.users.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5',
            'phone' => 'required',
        ]);

        if ($validator->passes()) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'User added successfully');

            return response()->json([
                'status' => true,
                'message' => 'User added successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    // Edit Page
    public function edit(Request $request, $id) {
        $user = User::find($id);

        if ($user == null) {
            session()->flash('error', 'User not found');
            return redirect()->route('users.index');
        }

        return view('admin.users.edit', [
            'user' => $user
        ]);
    }

    // Update User Logic
    public function update(Request $request, $id) {
        $user = User::find($id);

        if ($user == null) {
            session()->flash('error', 'User not found');
            return response()->json([
                'status' => true,
                'message' => 'User not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'phone' => 'required',
        ]);

        if ($validator->passes()) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;

            if ($request->password != "") {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            session()->flash('success', 'User updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // Delete User Logic
    public function destroy($id) {
        $user = User::find($id);

        if ($user == null) {
            session()->flash('error', 'User not found');
            return response()->json([
                'status' => true,
                'message' => 'User not found'
            ]);
        }

        $user->delete();

        session()->flash('success', 'User deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}

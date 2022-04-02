<?php

namespace App\Http\Controllers;

use App\Models\Bio;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    function signUp(Request $request)
    {
        $roleUserId = Role::select('id')->firstWhere('name', 'user');
        $avatar = 'profile_picture/1024px-User-avatar.png';

        $newUser = User::create([
            'email' => $request->input('email'),
            'avatar' => $avatar,
            'fullname' => $request->input('fullname'),
            'slug' => Str::slug($request->input('fullname'), ''),
            'password' => Hash::make($request->input('password')),
            'role_id' => $roleUserId->id,
            'account_status' => 1
        ]);

        Auth::login($newUser);

        return response()->json([
            'status_code' => 200,
            'user' => User::find(Auth::user()->id),
        ]);
    }

    function login()
    {
        // phải đăng nhập mới tạo được token => các request sau sẽ cần token này để tạo request(API TOKENS)
        if (!Auth::attempt(['email' => request('email'), 'password' => request('password')]))
            return response()->json(['error' => 'Địa chỉ email hoặc mật khẩu không chính xác!'], 401);

        // $tokenResult = Auth::user()->createToken('token');
        // request()->session()->regenerate(); // Session fixation

        return $this->getCurrentUser();
    }

    function getCurrentUser()
    {
        return response()->json([
            'status_code' => 200,
            'user' => Auth::user(),
        ]);
    }

    function logout()
    {
        // Auth::user()->tokens()->delete();
        // Auth::guard('web')->logout();

        // request()->session()->flush();
        Auth::guard('web')->logout();

        return [
            'message' => 'Đã đăng xuất',
            'session' => request()->session()->all(),
            'user' => Auth::user()
        ];
    }
}

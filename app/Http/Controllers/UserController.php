<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UserController extends Controller
{
    function signUp(Request $request)
    {
        $request->validate([
            'fullname' => 'string|required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $roleUserId = Role::select('id')->firstWhere('name', 'user');
        $avatar = 'profile_picture/1024px-User-avatar.png';

        $email = $request->input('email');

        $existUser = User::firstWhere('email', $email);

        if ($existUser) return response(['message' => 'Tài khoản đã tồn tại!'], 400);

        $newUser = User::create([
            'email' => $email,
            'avatar' => $avatar,
            'fullname' => $request->input('fullname'),
            'slug' => Str::slug($request->input('fullname'), ''),
            'password' => Hash::make($request->input('password')),
            'role_id' => $roleUserId->id,
            'account_status' => 1
        ]);

        Auth::login($newUser);

        if (!empty($session_id)) {
            $user_id = Auth::user()->id;
            Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);
        }

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

        // Được tạo khi thêm khóa học vào giỏ hàng
        $session_id = Session::get('anonymous_cart');

        if (!empty($session_id)) {
            $user_id = Auth::user()->id;

            $existedUserCart = Cart::firstWhere('user_id', $user_id);

            if (!$existedUserCart) {
                Cart::where('session_id', $session_id)->update(['user_id' => $user_id]);

                if (Auth::check()) {
                    $coursePurchased = Cart::with([
                        'course' => fn ($q) => $q->setEagerLoads([])->select('id', 'author_id'),
                        'course.course_bill' => fn ($q) => $q->select('course_id')
                    ])
                        ->where('user_id', $user_id)
                        ->where(function ($q) use ($user_id) {
                            $q
                                ->whereHas('course', function ($q) use ($user_id) {
                                    $q->where('author_id', $user_id);
                                })
                                ->orWhereHas('course.course_bill', function ($q) use ($user_id) {
                                    $q->where('user_id', $user_id);
                                });
                        })
                        ->get();

                    $arrCourseId = $coursePurchased->pluck('course_id');
                    Cart::where('user_id', $user_id)
                        ->whereIn('course_id', $arrCourseId)
                        ->delete();
                }
            }
        }

        return $this->getCurrentUser();
        // $tokenResult = Auth::user()->createToken('token');
        // request()->session()->regenerate(); // Session fixation

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
        Session::forget('anonymous_cart');

        return [
            'message' => 'Đã đăng xuất',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    function login()
    {
        return ['hi' => true];
        // if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
        //     return response();
        // } else {
        //     return response()->json(['error' => 'Unauthorised'], 401);
        // }
    }
}

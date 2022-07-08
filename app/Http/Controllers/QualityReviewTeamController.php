<?php

namespace App\Http\Controllers;

use App\Models\QualityReviewTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class QualityReviewTeamController extends Controller
{
    function get()
    {
        return response([
            'items' =>
            User::with(
                [
                    'quality_review_team' => fn ($q) => $q->with('category')
                ]
            )
                ->whereHas('role', fn ($q) => $q->where('name', 'quality_review'))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
        ]);
    }

    function create(Request $request)
    {
        $request->validate([
            'email' => 'email|required|unique:users,email',
            'fullname' => 'required',
            'categories' => 'required|array',
            'categories.*' => 'required|numeric'
        ]);

        $email = $request->input('email');
        $fullname = $request->input('fullname');
        $categories = $request->input('categories');

        // try {
        $userId = User::insertGetId(
            [
                'email' => $email,
                'fullname' => $fullname,
                'password' => Hash::make('123'), 'role_id' => 3,
                'slug' => Str::slug($fullname . '-' . Str::random(5), '-')
            ]
        );

        // foreach ($categories as $categoryId) {
        //     QualityReviewTeam::create(['user_id' => $userId, 'category_id' => $categoryId]);
        // }

        //     return response('success');
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return response('Lỗi trong quá trình lưu thông tin!', 400);
        // }
    }
}

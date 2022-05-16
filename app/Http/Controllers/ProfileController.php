<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\AuthorBiography;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    function uploadAvatar($request)
    {
        $request->validate(['file' => 'image']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $user = Auth::user();
            $filename = $user->id . '_avatar' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_picture', $filename);

            User::where('id', $user->id)->update(['avatar' => $path]);

            return response(['success' => true, 'path' => $path]);
        };
    }

    function changePassword($request)
    {
        $request->validate([
            'old_password' => ['max:30'],
            'new_password' => [
                // Password::mixedCase(),
                'max:30',
            ]

        ]);

        if (Hash::check($request->input('old_password'), Auth::user()->password)) {
            User::where('id', Auth::user()->id)
                ->update([
                    'password' => Hash::make($request->input('new_password')),
                ]);
            return response(['success' => true]);
        } else {
            return back()
                ->withErrors(
                    ['message_password' => 'Mật khẩu vừa nhập không chính xác!']
                );
        }
    }

    function changeProfile(Request $request)
    {
        Validator::make(
            $request->all(),
            [
                'bio' => function ($attribute, $value, $fail) {
                    if (str_word_count($value) < 15) {
                        $fail('Mô tả khóa học cần tối thiểu 15 từ.');
                    }
                },
                'headline' => 'string|nullable',
                'youtube' => 'string|nullable',
                'facebook' => 'string|nullable',
                'twitter' => 'string|nullable',
                'website' => 'nullable|url',
                'linkedin' => 'string|nullable',
                'fullname' => [
                    'nullable',
                    'min:3',
                    'max:256',
                    'string',
                ],
            ],
            [
                'fullname.min' => 'Tên của bạn phải đạt ít nhất :min kí tự',
                'fullname.max' => 'Tên của bạn đã vượt quá :max kí tự cho phép.',
                'website.url' => 'Bạn phải nhập một URL hợp lệ.'
            ]
        )
            ->validate();

        $data = collect($request->only(['headline', 'website', 'youtube', 'twitter', 'instagram', 'bio', 'linkedIn', 'facebook', 'fullname']))
            ->filter()
            ->toArray();

        if (count($data)) {
            User::where('id', Auth::user()->id)
                ->update($data);

            return response(['success' => true]);
        }
    }

    function checkInstructorProfileBeforePublishCourse()
    {
        $missingRequirements = Validator::make(
            collect(Auth::user())->toArray(),
            [
                'avatar' => 'required',
                'bio' => new AuthorBiography,
            ],
            [
                'avatar.required' => 'Mỗi giảng viên cần upload một hình ảnh đại diện.'
            ]
        )
            ->errors();

        return response(compact('missingRequirements'));
    }
}

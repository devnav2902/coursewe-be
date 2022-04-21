<?php

namespace App\Http\Controllers;

use App\Models\Bio;
use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ProfileController extends Controller
{
    public function uploadAvatar($request)
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
    public function save(Request $request)
    {
        $this->uploadAvatar($request);

        $request->validate([

            'fullname' => [
                'min:3',
                'max:256',
                'string',
            ],

        ]);
        User::where('id', Auth::user()->id)
            ->update([
                'fullname' => $request->input('fullname'),

            ]);
        if ($request->has(['old_password', 'new_password'])) {
            $this->changePassword($request);
        }
        $this->changeBio($request);

        return response(['success' => true]);
    }



    public function changePassword($request)
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
    public function changeBio($request)
    {
        $request->validate([
            'bio' => [],
            'headline' => [],
            'youtube' => 'url|nullable',
            'facebook' => 'url|nullable',
            'twitter' => 'url|nullable',
            'website' => 'url|nullable',
            'linkedin' => 'url|nullable'

        ]);

        $data = collect($request->only(['headline', 'website', 'youtube', 'twitter', 'instagram', 'bio', 'linkedIn', 'facebook']))
            ->filter()
            ->toArray();

        $user = Bio::select('user_id')
            ->firstWhere('user_id', Auth::user()->id);

        if ($user) {
            Bio::where('user_id', $user->user_id)
                ->update($data);
            // return back();
            return response(['success' => true]);
        }

        $data['user_id'] = Auth::user()->id;
        Bio::create($data);
        // return back();
        return response(['success' => true]);
    }
    public function getBio()
    {
        $bio = Bio::where('user_id', Auth::user()->id)->first();
        return response(['bio' => $bio]);
    }
}

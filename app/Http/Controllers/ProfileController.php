<?php

namespace App\Http\Controllers;

use App\Models\Bio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ProfileController extends Controller
{
    public function uploadAvatar(Request $request)
    {

        $request->validate(['file' => 'required|image']);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $user = Auth::user();
            $filename = $user->id . '_avatar' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_picture', $filename);


            User::where('id', $user->id)->update(['avatar' => $path]);

            return $path;
        } else {
            return back()
                ->withErrors(
                    ['message' => "Haven't selected an avatar yet!"]
                );
        }
        return redirect()->route('profile');
    }
    public function save(Request $request)
    {
        $request->validate([
            // 'email' => [
            //     'required',
            //     'unique:App\Models\User,email',
            //     'max:255',
            //     'email:rfc'
            // ],
            'fullname' => [
                'min:3',
                'max:256',
                'string',
                // 'regex:/[a-z]/'
            ],

        ]);

        User::where('id', Auth::user()->id)
            ->update([
                'fullname' => $request->input('fullname'),
                // 'email' => $request->input('email')
            ]);

        return back();
    }



    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required', 'max:30',],
            'new_password' => [
                'required',
                Password::min(1)->mixedCase(),
                'max:30',
            ]

        ]);

        if (Hash::check($request->input('old_password'), Auth::user()->password)) {
            User::where('id', Auth::user()->id)
                ->update([
                    'password' => Hash::make($request->input('new_password')),
                ]);
            return back();
        } else {
            return back()
                ->withErrors(
                    ['message_password' => 'Mật khẩu vừa nhập không chính xác!']
                );
        }
    }
    public function changeBio(Request $request)
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
            return back();
        }

        $data['user_id'] = Auth::user()->id;
        Bio::create($data);
        return back();
    }
    public function getBio()
    {
        $bio = Bio::where('user_id', Auth::user()->id)->first();
        return response(['bio' => $bio]);
    }
}

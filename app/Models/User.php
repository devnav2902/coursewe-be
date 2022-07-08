<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $with = ['role'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'fullname',
        'slug',
        'email',
        'password',
        'avatar',
        'email_verified_at',
        'account_status',
        'role_id',
        'bio',
        'headline',
        'youtube',
        'facebook',
        'twitter',
        'website',
        'phoneNumber',
        'cityCode',
        'city',
        'districtCode',
        'district',
        'wardCode',
        'ward',
        'address',
        'dob',
        'gender',
        'nation',
        'identification',
        'workCityCode',
        'workCity',
        'workDistrictCode',
        'workDistrict',
        'workWardCode',
        'workWard',
        'workAddress',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function course()
    {
        return $this->hasMany(Course::class, 'author_id');
    }
    public function enrollment()
    {
        return $this->hasMany(CourseBill::class, 'user_id');
    }

    function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
    function progress_logs()
    {
        return $this->hasMany(ProgressLogs::class, 'user_id');
    }
}

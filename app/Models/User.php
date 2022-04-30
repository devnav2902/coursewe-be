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
        'trangthai_taikhoan',
        'role_id',
        'created_at'
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

    function bio()
    {
        return $this->hasOne(Bio::class, 'user_id');
    }
}

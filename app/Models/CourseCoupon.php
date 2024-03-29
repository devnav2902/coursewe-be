<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCoupon extends Model
{
    use HasFactory;

    protected $table = 'course_coupon';
    protected $fillable =
    [
        'course_id',
        'coupon_id',
        'status',
        'code',
        'expires',
        'enrollment_limit',
        'discount_price'
    ];
    public $timestamps = false;
    protected $with = ['coupon'];

    function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    // function getExpiresAttribute($expires)
    // {
    //     return Carbon::parse($expires)->isoFormat('DD/MM/YYYY HH:mm A');
    // }

    // function getCreatedAtAttribute($created_at)
    // {
    //     return Carbon::parse($created_at)->isoFormat('DD/MM/YYYY HH:mm A');
    // }
}

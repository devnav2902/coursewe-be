<?php

namespace App\Models;

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
}

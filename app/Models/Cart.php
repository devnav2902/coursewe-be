<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'cart';
    protected $with = ['cartType', 'course'];
    protected $fillable = ['user_id', 'course_id', 'cart_type_id', 'coupon_code', 'session_id'];

    function cartType()
    {
        return $this->belongsTo(CartType::class, 'cart_type_id');
    }

    function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    function course_coupon()
    {
        return $this->belongsTo(CourseCoupon::class, 'course_id', 'course_id')
            ->select(
                "code",
                "course_id",
                "discount_price",
                "status",
                'coupon_id'
            )
            ->without('coupon')
            ->where('code', $this->coupon_code);
    }
}

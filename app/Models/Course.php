<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'course';
    protected $fillable = ['author_id', 'title', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'price', 'discount', 'submit_for_review'];
    protected $with =
    [
        'author',
        'rating',
        'course_bill',
        // 'price'
    ];

    function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    function coupon()
    {
        return $this->hasMany(CourseCoupon::class, 'course_id')
            ->where('status', 1);
    }

    function rating()
    {
        return $this->hasMany(Rating::class, 'course_id');
    }

    function course_bill()
    {
        return $this->hasMany(CourseBill::class, 'course_id');
    }

    function section()
    {
        return $this->hasMany(Section::class, 'course_id')->orderBy('order', 'asc');
    }

    function lecture()
    {
        return $this->hasManyThrough(Lecture::class, Section::class, 'course_id', 'section_id');
    }
    // function price()
    // {
    //     return $this->belongsTo(Price::class, 'price_id');
    // }
}

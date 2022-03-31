<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'course';
    protected $fillable = ['author_id', 'title', 'subtitle', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'price', 'discount', 'submit_for_review', 'instructional_level_id'];
    protected $with =
    [
        'author',
        'rating',
        'course_bill',
        'instructional_level'
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

    function instructional_level()
    {
        return $this->belongsTo(InstructionalLevel::class, 'instructional_level_id');
    }

    function course_outcome()
    {
        return $this->hasMany(CourseOutcome::class, 'course_id');
    }

    function course_requirements()
    {
        return $this->hasMany(CourseRequirements::class, 'course_id');
    }
    // function price()
    // {
    //     return $this->belongsTo(Price::class, 'price_id');
    // }
}

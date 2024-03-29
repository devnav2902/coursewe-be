<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Course extends Model
{
    use HasFactory;
    protected $table = 'course';
    protected $fillable = ['author_id', 'title', 'subtitle', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'price', 'discount', 'submit_for_review', 'instructional_level_id'];
    protected $with =
    [
        'author',
        'instructional_level',
        'price',
        'course_outcome',
        'course_requirements',

    ];

    protected $appends = ['is_purchased'];

    protected function isPurchased(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!Auth::check()) return false;
                return $this->course_bill()
                    ->where('user_id', Auth::user()->id)
                    ->select('user_id', 'course_id')
                    ->first();
            }
        );
    }


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

    function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    function categories()
    {
        return $this->belongsToMany(Categories::class, CategoriesCourse::class, 'course_id', 'category_id');
    }
    function progress_logs()
    {
        return $this->hasMany(ProgressLogs::class, 'course_id');
    }
    function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date, 'Asia/Ho_Chi_Minh')->isoFormat('DD/MM/YYYY HH:mm A');
    }

    function rating_quality()
    {
        return $this->hasMany(RatingQuality::class, 'course_id');
    }
}

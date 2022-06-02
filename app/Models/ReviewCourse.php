<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewCourse extends Model
{
    use HasFactory;
    protected $table = 'review_course';
    protected $fillable = ['course_id'];


    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->isoFormat('DD/MM/YYYY');
    }
}

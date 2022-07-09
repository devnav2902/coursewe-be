<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingQuality extends Model
{
    use HasFactory;
    protected $table = 'rating_quality';
    protected $fillable = ['user_id', 'course_id', 'rating'];
    protected $with = ['user'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}

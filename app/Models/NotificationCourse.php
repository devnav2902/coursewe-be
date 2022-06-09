<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotificationCourse extends Model
{
    use HasFactory;

    protected $table = 'notification_course';
    protected $fillable = ['notification_id', 'course_id'];
    protected $with = ['course'];

    function course()
    {
        return $this->belongsTo(Course::class, 'course_id')
            ->select('author_id', 'id', 'title', 'thumbnail')
            ->setEagerLoads([]);
    }
}

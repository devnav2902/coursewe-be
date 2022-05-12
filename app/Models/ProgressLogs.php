<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressLogs extends Model
{
    use HasFactory;
    protected $table = 'progress_logs';
    protected $fillable = ['lecture_id', 'user_id', 'last_watched_second', 'course_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}

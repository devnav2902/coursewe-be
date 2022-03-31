<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseOutcome extends Model
{
    use HasFactory;
    protected $table = 'course_outcome';
    protected $fillable = ['order', 'description', 'course_id'];
}

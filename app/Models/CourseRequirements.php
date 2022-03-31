<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRequirements extends Model
{
    use HasFactory;
    protected $table = 'course_requirements';
    protected $fillable = ['order', 'description', 'course_id'];
}

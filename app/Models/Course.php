<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $table = 'course';
    protected $fillable = ['author_id', 'title', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'price', 'discount', 'submit_for_review'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesCourse extends Model
{
    use HasFactory;

    protected $table = 'categories_course';

    function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}

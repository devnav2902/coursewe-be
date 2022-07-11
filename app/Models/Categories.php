<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    protected $fillable = ['title', 'slug', 'parent_id', 'category_id'];

    function course()
    {
        return $this->belongsToMany(Course::class, CategoriesCourse::class, 'category_id', 'course_id');
    }
}

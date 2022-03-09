<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;
    protected $table = 'progress';
    public $timestamps = false;
    protected $fillable = ['progress', 'user_id', 'lecture_id'];
}

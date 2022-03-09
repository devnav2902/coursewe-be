<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;
    protected $table = 'resources';
    protected $fillable = ['lecture_id', 'original_filename', 'src'];

    public $timestamps = false;
}

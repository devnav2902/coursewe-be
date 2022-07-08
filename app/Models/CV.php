<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CV extends Model
{
    use HasFactory;

    protected $table = 'cv';

    function user()
    {
        return $this->hasOne(User::class, 'user_id');
    }
}

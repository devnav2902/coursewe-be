<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bio extends Model
{
    use HasFactory;
    protected $table = 'bio';
    protected $fillable = ['user_id', 'twitter', 'headline', 'facebook', 'youtube', 'bio', 'linkedin', 'website'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

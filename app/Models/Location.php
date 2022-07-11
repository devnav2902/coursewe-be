<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'location';
    protected $fillable = ['country_code', 'user_id', 'ip', 'country', 'language'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

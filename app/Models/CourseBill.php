<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseBill extends Model
{
    use HasFactory;
    protected $table = 'course_bill';
    protected $fillable =
    ['course_id', 'user_id', 'title', 'thumbnail', 'price', 'promo_code', 'purchase'];

    function user()
    {
        $this->BelongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    function location()
    {
        return $this->hasOne(Location::class, 'user_id', 'user_id');
    }
}

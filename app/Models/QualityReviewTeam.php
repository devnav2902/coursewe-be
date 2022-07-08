<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityReviewTeam extends Model
{
    use HasFactory;

    protected $table = 'quality_review_team';
    protected $fillable = ['user_id', 'category_id'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    function category()
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationQualityReview extends Model
{
    use HasFactory;

    protected $table = 'notification_quality_review';
    protected $fillable = ['notification_id', 'admin_id'];

    function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

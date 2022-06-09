<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notification';
    protected $fillable = ['notification_entity_id', 'is_seen'];
    protected $with = ['notification_entity'];

    function notification_course()
    {
        return $this->hasOne(NotificationCourse::class, 'notification_id');
    }

    function notification_purchase()
    {
        return $this->hasOne(NotificationPurchase::class, 'notification_id');
    }

    function notification_quality_review()
    {
        return $this->hasOne(NotificationQualityReview::class, 'notification_id');
    }

    function notification_entity()
    {
        return $this->belongsTo(NotificationEntity::class, 'notification_entity_id');
    }
}

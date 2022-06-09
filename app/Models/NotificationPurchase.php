<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPurchase extends Model
{
    use HasFactory;

    protected $table = 'notification_purchase';
    protected $fillable = ['notification_id', 'course_bill_id'];
    protected $with = ['course_bill'];

    function course_bill()
    {
        return $this->belongsTo(CourseBill::class, 'course_bill_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEntity extends Model
{
    use HasFactory;

    protected $table = 'notification_entity';
    protected $fillable = ['type', 'text_start', 'text_end'];
}

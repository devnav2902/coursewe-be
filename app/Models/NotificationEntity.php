<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotificationEntity extends Model
{
    use HasFactory;

    protected $table = 'notification_entity';
    protected $fillable = ['type', 'text_start', 'text_end'];
    protected $with = ['role'];
    public $timestamps = false;

    function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

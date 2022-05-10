<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lecture extends Model
{
    use HasFactory;

    protected $table = 'lectures';

    protected $fillable = ['title', 'src', 'section_id', 'order', 'original_filename', 'playtime_string', 'playtime_seconds'];
    // protected $withCount = ['resource'];
    protected $with = ['resource'];

    // function progress()
    // {
    //     return $this->belongsToMany(User::class, Progress::class, 'lecture_id', 'user_id')
    //         ->withPivot('progress');
    // }

    function progress()
    {
        return $this->hasOne(Progress::class, 'lecture_id')
            ->where('user_id', Auth::user()->id)
            ->where('progress', 1);
    }

    function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->isoFormat('MM/DD/Y');
    }

    public function resource()
    {
        return $this->hasMany(Resource::class, 'lecture_id');
    }
    public function progress_logs()
    {
        return $this->hasMany(ProgressLogs::class, 'lecture_id');
    }
}

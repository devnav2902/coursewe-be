<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    function get()
    {
        $query = Notification::with([
            'notification_quality_review',
            'notification_course',
            'notification_purchase',
        ])
            ->orderBy('id', 'desc');


        $unread = $this->queryBase(Notification::where('is_seen', 0))->count();
        $notification = $this->queryBase($query)->paginate(10);

        return response(['notification' => $notification, 'unreadCount' => $unread]);
    }

    function markAsRead(Request $request)
    {
        $request->validate(['notification_id' => 'required|numeric']);

        Notification::where('id', $request->input('notification_id'))
            ->update(['is_seen' => 1]);

        return response(['message' => 'success']);
    }

    function markAllAsRead()
    {
        $this->queryBase(Notification::select('id'))->update(['is_seen' => 1]);

        return response(['message' => 'success']);
    }

    private function queryBase(Builder $builder)
    {
        return $builder->whereHas('notification_entity', function ($q) {
            $q->where('role_id', Auth::user()->role->id);
        })
            ->where(function ($q) {
                $q
                    ->whereHas('notification_purchase.course_bill', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->orWhereHas('notification_course.course', function ($q) {
                        $q->where('author_id', Auth::user()->id);
                    })
                    ->orWhereHas('notification_quality_review', function ($q) {
                        $q->where('admin_id', Auth::user()->id);
                    });
            });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    function get()
    {
        $query = Notification::with(['notification_course', 'notification_purchase'])
            ->orderBy('created_at', 'desc')
            ->whereHas('notification_purchase.course_bill', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })
            ->orWhereHas('notification_course.course', function ($q) {
                $q->where('author_id', Auth::user()->id);
            });

        $notification = (clone $query)->paginate(10);

        $unread = Notification::where('is_seen', 0)
            ->where(function ($q) {
                $q
                    ->whereHas('notification_purchase.course_bill', function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    })
                    ->orWhereHas('notification_course.course', function ($q) {
                        $q->where('author_id', Auth::user()->id);
                    });
            })
            ->count();

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
        Notification::where(function ($q) {
            $q
                ->whereHas('notification_purchase.course_bill', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })
                ->orWhereHas('notification_course.course', function ($q) {
                    $q->where('author_id', Auth::user()->id);
                });
        })
            ->update(['is_seen' => 1]);

        return response(['message' => 'success']);
    }
}

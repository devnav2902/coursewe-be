<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Notification;
use App\Models\NotificationCourse;
use App\Models\NotificationEntity;
use App\Models\ReviewCourse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    function reviewCourses()
    {
        $courses = ReviewCourse::with(['course' => function ($q) {
            $q->setEagerLoads([])->with(['author', 'price']);
        }])->paginate(10);

        return response(['courses' => $courses]);
    }

    function qualityReview(Request $request)
    {
        $request->validate([
            'courseId' => 'required|exists:course,id',
            'type' => 'required|exists:notification_entity,type'
        ]);

        $courseId = $request->input('courseId');
        $type = $request->input('type');

        try {
            if ($type === 'unapproved' || $type === 'approved') {
                $typeId = NotificationEntity::firstWhere('type', $type)->id;
                $notificationId = Notification::create(['notification_entity_id' => $typeId])->id;

                NotificationCourse::create(
                    ['notification_id' => $notificationId, 'course_id' => $courseId]
                );

                Course::where('id', $courseId)->update(
                    ['submit_for_review' => 0, 'isPublished' => $type === 'approved' ? 1 : 0]
                );

                return response(['message' => 'success']);
            }
        } catch (\Throwable $th) {
            return response(['message' => 'Lỗi trong quá trình xét duyệt khóa học!'], 400);
        }
    }
}

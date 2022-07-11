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

    function reviewCourses($limit = 5)
    {
        $courses = Course::where('submit_for_review', 1)
            ->orderBy('updated_at', 'desc')
            ->setEagerLoads([])
            ->with([
                'rating_quality' => function ($q) {
                    $q->with(['user' => fn ($q) => $q->without('role')->select('id', 'fullname', 'avatar')])
                        ->select('id', 'user_id', 'course_id', 'rating');
                },
                'price',
                'categories:category_id,title',
                'author:fullname,avatar,id,role_id'
            ])
            ->select('id', 'title', 'thumbnail', 'updated_at', 'author_id', 'price_id')
            ->withAvg('rating_quality', 'rating')
            ->paginate(10);

        $courses->getCollection()->transform(function ($item) {
            $ratings = $item->rating_quality->groupBy('rating');

            $ratingsData = [];
            $ratings->each(function ($rating, $key) use (&$ratingsData) {
                $ratingsData[] = ['rating' => $key, 'votes' => $rating->count()];
            });

            $item->ratings = $ratingsData;

            return $item;
        });

        return response(['courses' => $courses]);
    }

    function courseDetails($courseId)
    {
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

                ReviewCourse::where('course_id', $courseId)->delete();

                return response(['message' => 'success']);
            }
        } catch (\Throwable $th) {
            return response(['message' => 'Lỗi trong quá trình xét duyệt khóa học!'], 400);
        }
    }
}

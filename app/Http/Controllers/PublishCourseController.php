<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Notification as ModelsNotification;
use App\Models\NotificationCourse;
use App\Models\NotificationEntity;
use App\Models\NotificationQualityReview;
use App\Models\ReviewCourse;
use App\Models\User;
use App\Rules\AuthorBiography;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublishCourseController extends Controller
{
    function checkingPublishRequirements($course_id)
    {
        $course = Course::withCount(
            ['course_outcome', 'course_requirements', 'lecture', 'categories']
        )
            ->with('author', 'lecture')
            ->find($course_id);
        $course = collect($course)->toArray();

        $missingPublishRequirements = Validator::make($course, [
            "title" => "required",
            // "subtitle" => 'required',
            // "description" => function ($attribute, $value, $fail) {
            //     if (str_word_count($value) < 200) {
            //         $fail('Mô tả khóa học cần tối thiểu 200 từ.');
            //     }
            // },
            // 'lecture.*.src' => 'required',
            // "thumbnail" => 'required',
            // "video_demo" => 'required',
            // "course_outcome_count" => 'numeric|min:4',
            // "course_requirements_count" => 'numeric|min:0',
            // "lecture_count" => 'numeric|min:5',
            // 'categories_count' => 'numeric|min:1',
            // 'author.avatar' => 'required',
            // 'author.bio' => new AuthorBiography,
        ], [
            'title.required' => 'Bạn chưa nhập tiêu đề khóa học',
            // 'subtitle.required' => 'Bạn chưa nhập tóm tắt khóa học',
            // 'thumbnail.required' => 'Bạn cần thêm hình ảnh cho khóa học',
            // 'video_demo.required' => 'Bạn cần có một video giới thiệu khóa học',
            // 'course_outcome_count.min' => 'Bạn cần thêm ít nhất :min mục tiêu học tập trong khóa học của bạn',
            // 'course_requirements_count.min' => 'Bạn có thể thêm bất kỳ yêu cầu khóa học hoặc điều kiện tiên quyết cho khóa học',
            // 'lecture_count.min' => 'Khóa học cần có tối thiểu :min bài giảng.',
            // 'lecture.*.src' => 'Bạn chưa thêm nội dung cho bài giảng.',
            // 'categories_count.min' => 'Bạn chưa chọn danh mục cho khóa học của bạn.',
            // 'author.avatar.required' => 'Mỗi giảng viên cần upload một hình ảnh đại diện.'
        ])
            ->errors();

        if (count($missingPublishRequirements) < 1)
            return response()->json(['missingPublishRequirements' => null]);

        return response()->json(compact('missingPublishRequirements'));
    }

    function submitForReview(Request $request)
    {
        $request->validate(['courseId' => 'required|alpha_num']);

        try {
            $courseId = $request->input('courseId');

            $missingPublishRequirements = $this->checkingPublishRequirements($courseId)->getData()->missingPublishRequirements;

            if (!empty($missingPublishRequirements)) {
                return response(['message' => 'Lỗi trong quá trình gửi yêu cầu xét duyệt khóa học!'], 400);
            }

            $notificationEntity =
                NotificationEntity::whereIn('type', ['submit_for_review', 'quality_review'])->get();

            $submitForReviewId = $notificationEntity->where('type', 'submit_for_review')->first()->id;
            $submitForReviewIdCreated = ModelsNotification::insertGetId(
                [
                    'notification_entity_id' => $submitForReviewId
                ]
            );

            // Gửi thông báo cho giảng viên
            NotificationCourse::insert(
                ['notification_id' => $submitForReviewIdCreated, 'course_id' => $courseId]
            );

            $qualityReviewId = $notificationEntity->where('type', 'quality_review')->first()->id;
            // Gửi thông báo cho admin
            User::whereHas('role', fn ($q) => $q->where('id', 1))
                ->select('id')
                ->get()
                ->each(
                    function ($user) use ($qualityReviewId) {
                        $qualityReviewIdCreated = ModelsNotification::insertGetId(
                            [
                                'notification_entity_id' => $qualityReviewId
                            ]
                        );
                        NotificationQualityReview::insert(
                            [
                                'notification_id' => $qualityReviewIdCreated,
                                'admin_id' => $user->id
                            ]
                        );
                    }
                );

            ReviewCourse::updateOrCreate(['course_id' => $courseId]);
            Course::where('id', $courseId)->update(['submit_for_review' => 1]);

            return response(['message' => 'Yêu cầu xét duyệt khóa học thành công!', 'success' => true]);
        } catch (\Throwable $th) {
            return response(['message' => 'Gửi yêu cầu xét duyệt khóa học không thành công!'], 400);
        }
    }
}

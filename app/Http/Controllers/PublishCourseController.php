<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Rules\AuthorBiography;
use Illuminate\Support\Facades\Validator;

class PublishCourseController extends Controller
{
    function checkingPublishRequirements($course_id)
    {
        $course = Course::withCount(
            ['course_outcome', 'course_requirements', 'lecture', 'categories']
        )
            ->with('author')
            ->find($course_id);
        $course = collect($course)->toArray();

        $missingPublishRequirements = Validator::make($course, [
            "title" => "required",
            "subtitle" => 'required',
            "description" => function ($attribute, $value, $fail) {
                if (str_word_count($value) < 200) {
                    $fail('Mô tả khóa học cần tối thiểu 200 từ.');
                }
            },
            "thumbnail" => 'required',
            "video_demo" => 'required',
            "course_outcome_count" => 'numeric|min:4',
            "course_requirements_count" => 'numeric|min:0',
            "lecture_count" => 'numeric|min:5',
            'categories_count' => 'numeric|min:1',
            'author.avatar' => 'required',
            'author.bio' => new AuthorBiography,
        ], [
            'title.required' => 'Bạn chưa nhập tiêu đề khóa học',
            'subtitle.required' => 'Bạn chưa nhập tóm tắt khóa học',
            'thumbnail.required' => 'Bạn cần thêm hình ảnh cho khóa học',
            'video_demo.required' => 'Bạn cần có một video giới thiệu khóa học',
            'course_outcome_count.min' => 'Bạn cần thêm ít nhất :min mục tiêu học tập trong khóa học của bạn',
            'course_requirements_count.min' => 'Bạn có thể thêm bất kỳ yêu cầu khóa học hoặc điều kiện tiên quyết cho khóa học',
            'lecture_count.min' => 'Khóa học cần có tối thiểu :min bài giảng.',
            'categories_count.min' => 'Bạn chưa chọn danh mục cho khóa học của bạn.',
            'author.avatar.required' => 'Mỗi giảng viên cần upload một hình ảnh đại diện.'
        ])
            ->errors();

        return response(compact('missingPublishRequirements'));
    }
}

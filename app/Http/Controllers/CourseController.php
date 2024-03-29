<?php

namespace App\Http\Controllers;

use App\Models\CategoriesCourse;
use App\Models\Course;
use App\Models\CourseOutcome;
use App\Models\CourseRequirements;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

class CourseController extends Controller
{
    function checkUserHasPurchased($course_id)
    {
        $hasPurchased = false;
        if (Auth::check()) {
            $result = Auth::user()
                ->enrollment
                ->firstWhere('course_id', $course_id);

            $hasPurchased = $result ? true : false;
        }

        return response(['hasPurchased' => $hasPurchased]);
    }

    function checkUserHasRated($course_id)
    {
        $hasRated = false;
        if (Auth::check()) {
            $hasRated =
                Rating::where('user_id', Auth::user()->id)
                ->select('course_id')
                ->firstWhere('course_id', $course_id) ? true : false;
        }

        return response(['hasRated' => $hasRated]);
    }

    function bestSellingCourses()
    {
        $courses = Course::setEagerLoads([])
            ->with([
                'price',
                'instructional_level',
                'course_outcome:order,description,id,course_id',
                'course_requirements:order,description,id,course_id',
                'author' => fn ($q) =>
                $q->setEagerLoads([])->select('users.id', 'fullname', 'slug')
            ])
            ->where('isPublished', 1)
            ->orderBy('updated_at', 'desc')
            ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating'])
            ->having('course_bill_count', '>=', 5)
            ->withAvg('rating', 'rating')
            ->take(10)
            ->get();

        return response(compact('courses'));
    }

    function getLatestCourses()
    {
        $query = Course::where('isPublished', 1)
            ->setEagerLoads([])
            ->with([
                'price',
                'instructional_level',
                'course_outcome:order,description,id,course_id',
                'course_requirements:order,description,id,course_id',
                'author' => fn ($q) =>
                $q->setEagerLoads([])->select('users.id', 'fullname', 'slug')
            ])
            ->orderBy('created_at', 'desc')
            ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating'])
            ->withAvg('rating', 'rating')
            ->take(10);

        $queryLatestCourses = clone $query;
        $latestCourses = $queryLatestCourses
            ->get()
            ->map(function ($course) {
                $course->course_outcome = $course->course_outcome->take(3);
                return $course;
            });

        return response()->json(compact('latestCourses'));
    }

    function deleteCourseOutcome($course_id, Request $req)
    {
        if ($req->has('delete_course_outcome_order')) {
            $delete_course_outcome_order = $req->input('delete_course_outcome_order');

            CourseOutcome::where('course_id', $course_id)
                ->whereIn('order', $delete_course_outcome_order)
                ->delete();
            return response('success');
        }
        // return response('fail');
    }

    function deleteCourseRequirements($course_id, Request $req)
    {
        if ($req->has('delete_course_requirements_order')) {
            $delete_course_requirements_order = $req->input('delete_course_requirements_order');

            CourseRequirements::where('course_id', $course_id)
                ->whereIn('order', $delete_course_requirements_order)
                ->delete();
            return response('success');
        }
        // return response('fail');
    }

    function updateCourseOutcome($id, Request $req)
    {
        $dataUpdateOutcome = $req->input('outcome_items');

        if ($dataUpdateOutcome) {
            Validator::make($dataUpdateOutcome, [
                '*.description' => 'required',
            ], ['*.description.required' => 'Không được bỏ trống!'])
                ->validate();
            // $existed = CourseOutcome::firstWhere('course_id', $id);

            foreach ($dataUpdateOutcome as $outcome) {
                CourseOutcome::updateOrCreate(
                    [
                        'course_id' => $id, 'order' => $outcome['order']
                    ],
                    $outcome
                );
            }
            // }

            return response('success');
        }
    }

    function updateCourseRequirements($id, Request $req)
    {
        $dataUpdateRequirements = $req->input('requirement_items');

        if ($dataUpdateRequirements) {
            Validator::make($dataUpdateRequirements, [
                '*.description' => 'required',
            ], ['*.description.required' => 'Không được bỏ trống!'])
                ->validate();
            // $existed = CourseRequirements::firstWhere('course_id', $id);

            foreach ($dataUpdateRequirements as $requirement) {
                CourseRequirements::updateOrCreate(
                    [
                        'course_id' => $id, 'order' => $requirement['order']
                    ],
                    $requirement
                );
            }
            // }

            return response('success');
        }
    }

    function updateInformation(Request $req, $courseId)
    {
        // Thêm validation instructor

        $data = $req->only(['author_id', 'title', 'subtitle', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'instructional_level_id']);

        Validator::make($req->all(), [
            "description" => function ($attribute, $value, $fail) {
                if (str_word_count($value) < 200) {
                    $fail('Mô tả khóa học cần tối thiểu 200 từ.');
                }
            },
            "topic" => function ($attribute, $value, $fail) {
                if ((is_array($value) && count($value) < 1) || empty($value)) {
                    $fail('Bạn chưa chọn chủ đề dạy trong khóa học!');
                }
            },
        ])
            ->validate();

        // $data = collect($req->input())->except(['thumbnail', 'video_demo'])->filter();
        Course::where('id', $courseId)->update($data);

        if ($req->has('topic')) {
            $topic = $req->input('topic');

            CategoriesCourse::where('course_id', $courseId)->delete();
            if (!is_array($topic)) {
                CategoriesCourse::insert([
                    'course_id' => $courseId,
                    'category_id' => $topic,
                ]);
            } else {
                $dataToCreate = collect($topic)
                    ->map(function ($topic_id) use ($courseId) {
                        return [
                            'category_id' => $topic_id,
                            'course_id' => $courseId
                        ];
                    })
                    ->toArray();

                CategoriesCourse::insert($dataToCreate);
            }
        }

        return response('success');

        // CategoriesCourse::where('course_id', $course_id)->delete();

        // if ($request->has('category')) {
        //     $arr = [];

        //     foreach ($request->input('category') as  $value) {
        //         $arr[] = ['category_id' => $value, 'course_id' => $course_id];
        //     }

        //     CategoriesCourse::insert($arr);
        // }

    }

    function getCourse(Request $req)
    {
        $query =  Course::where('isPublished', 1)
            ->orderBy('created_at', 'desc');

        if ($req->has('limit')) {
            $course = $query->paginate($req->input('limit', 10));
        } else {
            $course = $query->get();
        }

        return $course;
    }

    function getCourseOfAuthorById($id)
    {

        $course = Course::where('id', $id)
            ->with([
                'lecture',
                'section',
                'progress_logs'
            ])
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->firstWhere('author_id', Auth::user()->id);


        if (!$course) abort(404);

        return response()->json(['course' => $course]);
    }

    function coursePreview($id)
    {
        validator(['id' => $id], ['id' => 'required'])->validate();

        $course = Course::with(['lecture', 'section'])
            ->withAvg('rating', 'rating')
            ->firstWhere('id', $id);

        if (empty($course)) return response(['Không tồn tại khóa học này'], 403);
        return response(compact('course'));
    }

    function getCourseById($id)
    {
        validator(['id' => $id], ['id' => 'required'])->validate();

        $course = Course::where('isPublished', 1)
            ->with(['lecture', 'section'])
            ->withAvg('rating', 'rating')
            ->firstWhere('id', $id);

        if (empty($course)) return response(['Không tồn tại khóa học này'], 403);
        return response($course);
    }
    function getDraftCourseById($id)
    {
        validator(['id' => $id], ['id' => 'required'])->validate();

        return Course::with(['lecture', 'section'])
            ->withAvg('rating', 'rating')
            ->firstWhere('id', $id);
    }

    function getCourseBySlug($slug)
    {
        $course = Course::where('slug', $slug)
            ->where('isPublished', 1)
            ->with([
                'lecture',
                'section',
                'course_requirements',
                'course_outcome',
            ])
            ->withAvg('rating', 'rating')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->first();

        if (empty($course)) abort(404);

        // $course->setRelation('rating', $course->rating()->paginate(10));

        // RATING
        $graph = $this->ratingGraph($course);

        return response()->json(compact('graph', 'course'));
    }

    private function createObj($rating, $percent)
    {
        $obj = new stdClass;
        $obj->rating = $rating;
        $obj->percent = $percent;
        return $obj;
    }

    private function ratingGraph($course)
    {
        $graph = [];

        for ($i = 1; $i <= 4; $i++) {
            $count_rating = $course
                ->rating
                ->where('rating', $i)
                ->count();

            $percent = 0;
            if ($course->rating_count) {
                $percent = ROUND(($count_rating * 100 / $course->rating_count), 1);
            }

            $graph[] = $this->createObj($i, $percent);
        }

        $sum = collect($graph)->sum('percent');
        $rest = 0;

        if (count($course->rating)) $rest = 100 - $sum;

        $graph[] = $this->createObj(5, $rest);

        return $graph;
    }
}

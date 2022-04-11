<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseOutcome;
use App\Models\CourseRequirements;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use stdClass;

class CourseController extends Controller
{
    function getLatestCourses()
    {
        $query = Course::without(['section', 'course_bill'])
            ->where('isPublished', 1)
            ->orderBy('created_at', 'desc')
            ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating'])
            ->withAvg('rating', 'rating')
            ->take(15);

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
        $dataUpdateOutcome = $req->input('course_outcome');

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
        }


        return response('success');
    }

    function updateCourseRequirements($id, Request $req)
    {
        $dataUpdateRequirements = $req->input('course_requirements');

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
        }


        return response('success');
    }

    function updateInformation($id, Request $req)
    {
        // Thêm validation instructor
        $data  = $req->only(['author_id', 'title', 'subtitle', 'description', 'slug', 'thumbnail', 'video_demo', 'isPublished', 'instructional_level_id']);

        // $data = collect($req->input())->except(['thumbnail', 'video_demo'])->filter();
        Course::where('id', $id)->update($data);

        if ($req->hasFile('thumbnail')) {
            $image = $req->file('thumbnail');
            $name = $image->getClientOriginalName();
            $path =  $image->storeAs('thumbnail', time() . $name);

            Course::where('id', $id)
                ->update(['thumbnail' => $path]);
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
    function getCourseByCurrentUser()
    {
        $courses = Course::where('author_id', Auth::user()->id)
            ->where('isPublished', 1)
            ->get();

        return response(['courses' => $courses]);
    }
    function getCourseOfAuthorById($id)
    {

        $course = Course::where('id', $id)
            ->with([
                'lecture',
                'section'
            ])
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->firstWhere('author_id', Auth::user()->id);


        if (!$course) abort(404);

        return response()->json(['course' => $course]);
    }
    function getCourseById($id)
    {
        validator(['id' => $id], ['id' => 'required'])->validate();

        return Course::where('isPublished', 1)
            ->withAvg('rating', 'rating')
            ->firstWhere('id', $id);
    }

    function getCourseBySlug(Request $req)
    {
        $req->validate([
            'slug' => 'required'
        ]);

        $course = Course::where('slug', $req->slug)
            ->where('isPublished', 1)
            ->with([
                'lecture',
                'section',
                'course_requirements',
                'course_outcome'
                // 'lecture.progress' => function ($q) {
                //     $q->where('progress', 1);
                // }
            ])
            ->withAvg('rating', 'rating')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->get();

        if (!count($course)) abort(404);

        // $course->transform(function ($course) {
        //     $course->setRelation('rating', $course->rating()->paginate(10));

        //     $helper = new HelperController;
        //     $count = $helper->countProgress($course->lecture);
        //     $course->count_progress = $count;
        //     return $course;
        // });

        $course = $course[0];

        $hasPurchased = false;
        $hasCommented = false;
        // $isFree = $course->price->price === 0.0 ? true : false;

        if (Auth::check()) {
            $result = Auth::user()
                ->enrollment
                ->firstWhere('course_id', $course->id);

            $hasPurchased = $result ? true : false;

            $hasCommented =
                Rating::where('user_id', Auth::user()->id)
                ->select('course_id')
                ->firstWhere('course_id', $course->id) ? true : false;
        }

        // RATING
        $graph = $this->ratingGraph($course);

        return response()->json(compact('graph', 'course', 'hasCommented', 'hasPurchased'));

        // if ($request->isMethod('GET'))
        //     return view('pages.course-lesson', compact(['graph', 'course', 'isPurchased', 'isFree']));

        // $code = $request->input('coupon-input');

        // $helper = new HelperController;

        // $coupon = $helper->getCoupon($code, $course->id);

        // $couponJSON = collect($coupon)
        //     ->only(['course_id', 'coupon_id', 'code', 'discount_price'])->toJson();

        // $saleOff = 100;
        // $isFreeCoupon = false;

        // if ($coupon) {
        //     $original_price = $course->price->price;
        //     $discount_price = $coupon->discount_price;

        //     $total = $original_price - $discount_price;
        //     if ($total == $original_price) $isFreeCoupon = true;
        //     else $saleOff = round(($total / $original_price) * 100);
        // }

        // return view(
        //     'pages.course-lesson',
        //     compact(
        //         ['isPurchased', 'course', 'graph', 'coupon', 'couponJSON', 'saleOff', 'isFreeCoupon', 'isFree']
        //     )
        // );
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

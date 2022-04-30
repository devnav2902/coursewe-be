<?php

namespace App\Http\Controllers;

use App\Models\CategoriesCourse;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{

    private $helperController;

    function __construct()
    {
        $this->helperController = new HelperController();
    }

    function featuredCoursesByCategoryId($topLevelCategoryId)
    {
        $where = "t1.category_id = " . $topLevelCategoryId;
        $category_query = $this->helperController->queryCategory($where);
        $result = DB::select($category_query);

        $topics_id = collect($result)->map(function ($level) {
            if ($level->topic_id) return $level->topic_id;

            return $level->subcategory_id;
        });

        $queryGetCourses = Course::whereHas('categories', function ($query) use ($topics_id) {
            $query->whereIn('categories_course.category_id', $topics_id);
        })
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->withAvg('rating', 'rating')
            ->having('rating_avg_rating', ">=", 4.0)
            ->with(['categories:category_id,parent_id,title,slug', 'course_outcome'])
            ->take(10);

        return response()->json(['courses' => $queryGetCourses->get()]);
    }

    function featuredCourses($limit = 10)
    {
        $queryGetCourses = Course::select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->without(['course_bill', 'rating'])
            ->withAvg('rating', 'rating')
            ->having('rating_avg_rating', '>=', 4.0)
            ->take($limit);

        $courses = $queryGetCourses->get();

        return response()->json(compact('courses'));
    }

    function featuredCategories($limit)
    {
        $queryGetCourses = CategoriesCourse::whereHas(
            'course',
            function ($q) {
                $q
                    ->setEagerLoads([])
                    ->select('id', 'title')
                    ->withAvg('rating', 'rating')
                    ->having('rating_avg_rating', '>=', 4.0);
            }
        );

        $featured_courses = $queryGetCourses->get();

        $categories_id = $featured_courses
            ->pluck('category_id')
            ->unique()
            ->values()
            ->toArray();
        $helperController = new HelperController();
        $topLevelCategories = $helperController->getTopLevelCategories($categories_id, $limit);

        return response()->json(
            [
                'topLevelCategories' => $topLevelCategories
            ]
        );
    }

    function getCoursesByCategorySlug($slug)
    {
        $helper = new HelperController();
        $courses = $helper->getCoursesByCategorySlug($slug);

        return response()->json(compact('courses'));
    }

    function amountCoursesInTopics($slug)
    {
        $helperController = new HelperController();
        $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

        $arr = $coursesBySlug->pluck('categories');

        $arrCategories = [];

        foreach ($arr as $category) {
            foreach ($category as $value) array_push($arrCategories, $value);
        }

        $counted = collect($arrCategories)->countBy(function ($category) {
            return $category['slug'];
        });

        $unique = collect($arrCategories)->unique('category_id');

        $topicsWithCourses = $unique
            ->map(function ($category) use ($counted) {
                $category['amount'] = $counted[$category['slug']];

                return $category;
            })
            ->values();

        return response(compact('topicsWithCourses'));
    }

    function getCategories()
    {
        $helperController = new HelperController();
        $category_query = $helperController->queryCategory("t1.parent_id IS NULL");

        $result = DB::select($category_query);

        $grouped_categories  = collect($result)
            ->groupBy(['level1', 'level2', 'level3']);

        $slug_categories = collect($result)->mapWithKeys(function ($item) {
            return [
                $item->level1 => $item->level1_slug,
                $item->level2 => $item->level2_slug,
                $item->level3 => $item->level3_slug,
            ];
        });

        $categories = [];

        foreach ($grouped_categories as $key_top_level => $top_level) {
            $slug_top_level = $slug_categories[$key_top_level];

            // subcategory(level 2)
            $data_subcategory = [];

            foreach ($top_level as $key => $subcategory) {
                // key can be null because level 3 may be not exist
                $array_keys_subcategory = array_keys($subcategory->all()); // get keys
                $arrayRemovedEmptyKey = array_filter($array_keys_subcategory); // remove "" key
                // topics(level 3)
                $topics =
                    collect($arrayRemovedEmptyKey)
                    ->map(function ($topic) use ($slug_categories) {
                        $dataTopic =  [
                            'name' => $topic,
                            'slug' => $slug_categories[$topic],
                        ];

                        return $dataTopic;
                    });
                $slug_subcategory = $slug_categories[$key];

                $data_topics = [
                    'name' => $key,
                    'slug' => $slug_subcategory,
                    'subcategory' => count($topics) ? $topics : null
                ];

                array_push($data_subcategory, $data_topics);
            }

            $data_top_level =  [
                'name' => $key_top_level,
                'slug' => $slug_top_level,
                'subcategory' => $data_subcategory
            ];

            array_push($categories, $data_top_level);
        }

        return response()->json(compact('categories'));
    }

    function getAmountCoursesByTypesPrice($slug)
    {
        $helperController = new HelperController();
        $courses = $helperController->getCoursesByCategorySlug($slug, false);
        $priceArr = $courses->pluck('price');

        $amountCoursesByTypesPrice = [
            'free' => ['amount' => 0, 'price_id' => null],
            'paid' => ['amount' => 0]
        ];

        foreach ($priceArr as $value) {
            if (intval($value['original_price']) !== 0)
                $amountCoursesByTypesPrice['paid']['amount'] += 1;
            else {
                $amountCoursesByTypesPrice['free']['amount'] += 1;
                $amountCoursesByTypesPrice['free']['price_id'] = $value['id'];
            }
        }

        return response()->json(compact('amountCoursesByTypesPrice'));
    }
    function getPopularInstructors($slug)
    {
        $helperController = new HelperController();
        $courses = $helperController->getCoursesByCategorySlug($slug, false);
        $collectionCourses = collect($courses)->where("rating_avg_rating", '>=', 4)->values();
        $author = $collectionCourses->pluck('author')->unique('id')->values();
        // $authorId = $author->pluck('id');

        $rating = $collectionCourses->groupBy('author_id');
        $avgRating = $rating->map(function ($course, $key) use ($author, $collectionCourses) {
            $avgRating =  number_format($course->avg('rating_avg_rating'), 1, '.', '.');
            $amountSudents = $course->sum('course_bill_count');
            $infoAuthor = collect($author)->where('id', $key)->first();
            $totalCourses = collect($collectionCourses)->where('author_id', $key)->count();
            return ['infoAuthor' => $infoAuthor, 'avgRating' => $avgRating, 'amountSudents' => $amountSudents, 'totalCourses' => $totalCourses];
        })->take(10)->values();
        return response()->json(compact('avgRating'));
    }
}

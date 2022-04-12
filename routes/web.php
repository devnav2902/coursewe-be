<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\HelperController;
use App\Models\Categories;
use App\Models\CategoriesCourse;
use App\Models\Course;
use App\Models\InstructionalLevel;
use App\Models\Price;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/course/latest', function () {
    $query = Course::without(['section', 'course_bill'])
        ->where('isPublished', 1)
        ->orderBy('created_at', 'desc')
        ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at')
        ->withCount(['course_bill', 'rating'])
        ->withAvg('rating', 'rating')
        ->take(6);

    $queryLatestCourses = clone $query;
    $latestCourses = $queryLatestCourses->get();

    return response()->json(compact('latestCourses'));
});

Route::get('/filter-rating/{slug}', function ($slug) {
    $helperController = new HelperController();
    $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

    $ratingArr = $coursesBySlug->pluck('rating_avg_rating');

    $data = [
        '4.5' => ['amount' => 0],
        '4.0' => ['amount' => 0],
        '3.5' => ['amount' => 0],
        '3.0' => ['amount' => 0],
    ];

    foreach ($ratingArr as $valueRating) {
        $value = floatval($valueRating);
        if ($value >= 4.5) {
            $data['4.5']['amount'] += 1;
        } else if ($value >= 4) {
            $data['4.0']['amount'] += 1;
        } else if ($value >= 3.5) {
            $data['3.5']['amount'] += 1;
        } else if ($value >= 3.0) {
            $data['3.0']['amount'] += 1;
        }
    }

    return $data;
});

Route::get('/category-managing', function () {
    $category_query =
        "SELECT t1.title AS level1,t1.slug AS level1_slug,
        t2.title  AS level2,t2.slug AS level2_slug,
        t3.title AS level3,t3.slug AS level3_slug
    FROM categories as t1
    LEFT JOIN categories AS t2 ON t1.category_id = t2.parent_id
    LEFT JOIN categories AS t3 ON t2.category_id = t3.parent_id
    WHERE t1.parent_id IS NULL";

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

    $sample = [
        'Ngoai ngu' => [
            'slug' => 'ngoai-ngu',
            'subcategory' => [
                'tienganh' => [
                    'slug' => 'tieng-anh',
                    'topics' => [
                        'toeic' => ['slug' => 'toeic'],
                        'ielts' => ['slug' => 'ielts']
                    ]
                ],
                'tiengphap' => [
                    'slug' => 'tieng-phap',
                    'topics' => [
                        'giaotiep' => ['slug' => 'giao-tiep'],
                    ]
                ],
            ],
        ],
        'CNTT' => [
            'slug' => 'CNTT',
            'subcategory' => [
                'web' => [
                    'slug' => 'web',
                    'topics' => null
                ],
            ],
        ],
    ];
    dd($sample);
    // $categories = $grouped_categories->map(function ($top_level, $key) use ($slug_categories) {

    //     $slug_top_level = $slug_categories[$key];
    //     $data_subcategory = $top_level->map(function ($subcategory, $key) use ($slug_categories) {
    //         // key can be null because level 3 may be not exist
    //         $array_keys_subcategory = array_keys($subcategory->all()); // get keys
    //         $arrayRemovedEmptyKey = array_filter($array_keys_subcategory); // remove "" key
    //         $topics =
    //             collect($arrayRemovedEmptyKey)
    //             ->map(function ($topic) use ($slug_categories) {
    //                 $dataTopic = [
    //                     $topic => [
    //                         'name' => $topic,
    //                         'slug' => $slug_categories[$topic],
    //                     ]
    //                 ];

    //                 return $dataTopic;
    //             });


    //         $slug_subcategory = $slug_categories[$key];
    //         $data_subcategory = [
    //             'slug' => $slug_subcategory,
    //             'topics' => count($topics) ? $topics : null
    //         ];

    //         return $data_subcategory;
    //     });


    //     $data_top_level =  [
    //         'slug' => $slug_top_level,
    //         'subcategory' => $data_subcategory
    //     ];

    //     return $data_top_level;
    // });

    $categories = [];

    foreach ($grouped_categories as $key => $top_level) {
        $slug_top_level = $slug_categories[$key];

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
                'topics' => count($topics) ? $topics : null
            ];

            array_push($data_subcategory, $data_topics);
        }

        $data_top_level =  [
            'name' => $key,
            'slug' => $slug_top_level,
            'subcategory' => $data_subcategory
        ];

        array_push($categories, $data_top_level);
    }


    return $categories;

    return $grouped_categories;

    // return response()->json(compact('slug_categories', 'grouped_categories'));
});

Route::get('/get-categories/{slug}', function ($slug) {
    $courses = Categories::with(['course'])
        ->firstWhere('slug', $slug);

    $courses->setRelation('course', $courses->course()->paginate(10));

    return $courses;
});

Route::get('/get-topics/{slug}', function ($slug) {
    function queryCategory($where)
    {
        return "SELECT t1.title AS level1_title,t1.slug AS level1_slug,
                    t2.title  AS level2_title,t2.slug AS level2_slug,
                    t3.title AS level3_title,t3.slug AS level3_slug
                FROM categories as t1
                LEFT JOIN categories AS t2 ON t1.category_id = t2.parent_id
                LEFT JOIN categories AS t3 ON t2.category_id = t3.parent_id WHERE " . $where;
    }

    $where = "t1.slug = '" . $slug . "' OR " . "t2.slug = '" . $slug . "' OR " . "t3.slug = '" . $slug . "'";
    $category_query = queryCategory($where);
    $result = DB::select($category_query);

    $topics_slug = collect($result)->map(function ($level) {
        if ($level->level3_slug)
            return $level->level3_slug;

        return $level->level2_slug;
    });

    // $courses = Course::whereHas('categories', function ($query) use ($topics_slug) {
    //     $query->whereIn('slug', $topics_slug);
    // })
    //     ->withCount(['course_bill', 'rating', 'section', 'lecture'])
    //     ->withAvg('rating', 'rating')

    //     ->paginate(5);
    DB::enableQueryLog();
    $courses = Course::whereHas('categories', function ($query) {
        $query->whereIn('categories_course.category_id', [23, 32]);
    })
        // ->withCount(['course_bill', 'rating', 'section', 'lecture'])
        // ->withAvg('rating', 'rating')

        // ->paginate(5);
        ->setEagerLoads([])
        ->select('id')
        ->get();
    return DB::getQueryLog();

    // return DB::select('SELECT * FROM `categories_course` WHERE category_id IN (23,32)');
    return $courses;
    // return $topics_slug;
    // $courses->setRelation('course', $courses->course()->paginate(10));

    return $topics_slug;
});

// Route::get('/level/{slug}', function ($slug) {
//     $helperController = new HelperController();
//     $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

//     $levels = InstructionalLevel::get();
//     $levelInCourses = $coursesBySlug->pluck('instructional_level');
//     $countCoursesByLevel = $levelInCourses->countBy('level');

//     $levels->transform(function ($level) use ($countCoursesByLevel) {
//         $name = $level['level'];
//         $amount = 0;

//         $data = ['name' => $name, 'id' => $level['id'], 'amount' => $amount];

//         if (isset($countCoursesByLevel[$name])) {
//             $amount = $countCoursesByLevel[$name];
//             $data['amount'] = $amount;
//         }

//         return $data;
//     });

//     return $levels;
// });

// Route::get('/price/{slug}', function ($slug) {
//     $helperController = new HelperController();
//     $courses = $helperController->getCoursesByCategorySlug($slug, false);
//     $priceArr = $courses->pluck('price');

//     $amountCoursesByTypesPrice = [
//         'free' => ['amount' => 0, 'price_id' => null],
//         'paid' => ['amount' => 0]
//     ];

//     foreach ($priceArr as $value) {
//         if (intval($value['original_price']) !== 0)
//             $amountCoursesByTypesPrice['paid']['amount'] += 1;
//         else {
//             $amountCoursesByTypesPrice['free']['amount'] += 1;
//             $amountCoursesByTypesPrice['free']['price_id'] = $value['id'];
//         }
//     }

//     return response()->json(compact('amountCoursesByTypesPrice'));
// Route::get('/topics/{slug}', function ($slug) {
//     $helperController = new HelperController();
//     $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

//     $arr = $coursesBySlug
//         ->pluck('categories');

//     $arrCategories = [];

//     foreach ($arr as $category) {
//         foreach ($category as $value) array_push($arrCategories, $value);
//     }

//     $counted = collect($arrCategories)->countBy(function ($category) {
//         return $category['slug'];
//     });

//     $unique = collect($arrCategories)->unique('category_id');

//     return $unique->map(function ($category) use ($counted) {
//         $category['amount'] = $counted[$category['slug']];

//         return $category;
//     })->values();
// });

Route::get('/courses/featured', function () {
    $queryGetCourses = Course::setEagerLoads([])
        ->select('id', 'title', 'thumbnail')
        ->withCount(['course_bill', 'rating', 'section', 'lecture'])
        ->withAvg('rating', 'rating')
        ->having('rating_avg_rating', '>=', 4.0);

    $courses = $queryGetCourses->get();

    return response()->json(compact('courses'));
});

Route::get('/courses/{topLevelCategoryId}', function ($topLevelCategoryId) {
    $where = "t1.category_id = " . $topLevelCategoryId;
    $helperController = new HelperController();
    $category_query = $helperController->queryCategory($where);
    $result = DB::select($category_query);

    $topics_id = collect($result)->map(function ($level) {
        if ($level->topic_id) return $level->topic_id;

        return $level->subcategory_id;
    });

    $queryGetCourses = Course::whereHas('categories', function ($query) use ($topics_id) {
        $query->whereIn('categories_course.category_id', $topics_id);
    })
        ->setEagerLoads([])
        // ->with(['categories:category_id,parent_id,title,slug'])
        ->select('id', 'slug', 'thumbnail')
        ->withCount(['section', 'lecture'])
        ->withAvg('rating', 'rating')
        ->having('rating_avg_rating', 4.0)
        ->take(10);

    return response()->json(['courses' => $queryGetCourses->get()]);
});


Route::get('/best-selling-courses', function () {
    return Course::select('id')
        ->orderBy('updated_at', 'desc')
        ->setEagerLoads([])
        ->withCount(['course_bill'])
        ->having('course_bill_count', '>=', 5)
        ->take(10)
        ->get();
});

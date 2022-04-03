<?php

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

    $categories = $grouped_categories->map(function ($top_level, $key) use ($slug_categories) {

        $slug_top_level = $slug_categories[$key];
        $data_subcategory = $top_level->map(function ($subcategory, $key) use ($slug_categories) {
            // key can be null because level 3 may be not exist
            $array_keys_subcategory = array_keys($subcategory->all()); // get keys
            $arrayRemovedEmptyKey = array_filter($array_keys_subcategory); // remove "" key
            $topics =
                collect($arrayRemovedEmptyKey)
                ->map(function ($topic) use ($slug_categories) {
                    $dataTopic = [
                        $topic => [
                            'name' => $topic,
                            'slug' => $slug_categories[$topic],
                        ]
                    ];

                    return $dataTopic;
                });


            $slug_subcategory = $slug_categories[$key];
            $data_subcategory = [
                'slug' => $slug_subcategory,
                'topics' => count($topics) ? $topics : null
            ];

            return $data_subcategory;
        });


        $data_top_level =  [
            'slug' => $slug_top_level,
            'subcategory' => $data_subcategory
        ];

        return $data_top_level;
    });

    // foreach ($grouped_categories as $key => $top_level) {
    //     $data = [
    //         $key => [
    //             'slug' => $slug_categories[$key],
    //             'subcategory' => $top_level->map(function ($subcategory, $key) use ($slug_categories) {
    //                 $slug_subcategory = $slug_categories[$key];

    //                 $topics = $subcategory->map(function ($key) use ($slug_categories) {
    //                     $dataTopic = [
    //                         'slug' => $slug_categories[$key],
    //                     ];
    //                     return $dataTopic;
    //                 });

    //                 $data_subcategory = [
    //                     'slug' => $slug_subcategory,
    //                     'topics' => $topics
    //                 ];
    //                 return $data_subcategory;
    //             })
    //         ]
    //     ];

    //     array_push($categories, $data);
    // }

    return $categories;

    return $grouped_categories;

    // return response()->json(compact('slug_categories', 'grouped_categories'));
});

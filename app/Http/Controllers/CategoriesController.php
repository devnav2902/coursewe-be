<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    function getCategories()
    {
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

        $categories = $grouped_categories->map(function ($top_level, $key) use ($slug_categories) {

            $slug_top_level = $slug_categories[$key];

            // subcategory(level 2)
            $subcategory = $top_level->map(function ($subcategory, $key) use ($slug_categories) {
                // key can be null because level 3 may be not exist
                $array_keys_subcategory = array_keys($subcategory->all()); // get keys
                $arrayRemovedEmptyKey = array_filter($array_keys_subcategory); // remove "" key
                // topics(level 3)
                $topics =
                    collect($arrayRemovedEmptyKey)
                    ->map(function ($topic) use ($slug_categories) {
                        $dataTopic = [
                            $topic => [
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
                'subcategory' => $subcategory
            ];

            return $data_top_level;
        });

        return response()->json(compact('categories'));
    }
}

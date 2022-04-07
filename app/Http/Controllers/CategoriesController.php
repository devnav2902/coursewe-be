<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    private function queryCategory($where)
    {
        return "SELECT t1.title AS level1,t1.slug AS level1_slug,
                t2.title  AS level2,t2.slug AS level2_slug,
                t3.title AS level3,t3.slug AS level3_slug
            FROM categories as t1
            LEFT JOIN categories AS t2 ON t1.category_id = t2.parent_id
            LEFT JOIN categories AS t3 ON t2.category_id = t3.parent_id WHERE " . $where;
    }

    function getCoursesByCategorySlug($slug)
    {
        $where = "t1.slug = '" . $slug . "' OR " . "t2.slug = '" . $slug . "' OR " . "t3.slug = '" . $slug . "'";
        $category_query = $this->queryCategory($where);
        $result = DB::select($category_query);

        $topics_slug = collect($result)->pluck('level3_slug')->filter();

        $courses = Course::whereHas('categories', function ($query) use ($topics_slug) {
            $query->whereIn('slug', $topics_slug);
        })
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->withAvg('rating', 'rating')
            ->with('course_outcome')
            ->paginate(5);

        return response()->json(compact('courses'));
    }

    function getCategories()
    {
        $category_query = $this->queryCategory("t1.parent_id IS NULL");

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
}

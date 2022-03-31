<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['PHP', 'JavaScript', 'HTML', 'CSS', 'Laravel', 'NodeJS', 'NestJS', 'ReactJS', 'AngularJS', 'VueJS', 'TypeScript', 'Jquery', 'Bootstrap'];

        foreach ($categories as $value) {
            DB::table('category')->insert(
                [
                    'title' => $value,
                    'isPublished' => 1,
                    'slug' => Str::slug($value),
                ]
            );
        }
    }
}

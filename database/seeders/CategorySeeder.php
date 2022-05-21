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
        $categories = [
            'Ngoại ngữ'  =>
            [
                'Tiếng anh' => ['Tiếng anh giao tiếp', 'IELTS', 'Ngữ pháp tiếng anh', 'TOEIC'],
                'Tiếng nhật' => ['Chứng chỉ tiếng nhật', 'Tiếng nhật cơ bản'],
                'Tiếng hàn' => ['Chứng chỉ tiếng hàn', 'Tự học tiếng hàn'],
                'Tiếng trung' => ['Chứng chỉ tiếng trung', 'Tiếng trung giao tiếp']
            ],

            'Phát triển bản thân' =>
            [
                'Kỹ năng giao tiếp',
                'Kỹ năng tư duy',
                'Kỹ năng lãnh đạo',
                'Tài chính cá nhân',
                'Kỹ năng quản lý thời gian',
            ],

            'Lập trình - CNTT' => [
                'Game Development' => ['Unity', 'C#', 'C++'],
                'Web Development' =>
                ['Javascript', 'React', 'CSS', 'Angular', 'NodeJS', 'HTML5', 'PHP', 'Bootstrap', 'VueJS', 'NestJS', 'Jquery', 'Typescript'],
                'Mạng máy tính',
                'Mobile Development' => ['Kotlin', 'SwiftUI', 'Google Flutter'],
            ],

            'Tin học văn phòng' => ['Excel', 'PowerPoint', 'Word'],

            'Âm nhạc' => [
                'Nhạc cụ' => ['Đàn Guitar', 'Đàn Piano', 'Đàn Ukulele'],
                'Sản xuất và sáng tác',
                'Luyện thanh'
            ],

            'Đầu tư' => [
                'Đầu tư chứng khoán',
                'Đầu tư tiền ảo - Crypto',
                'Đầu tư Forex',
                'Đầu tư bất động sản'
            ],

            'Marketing' =>
            [
                'Digital Marketing' => ['Google Ads', 'Tiktok', 'Content Marketing', 'Facebook Marketing'], 'Marketing cơ bản',
                'PR - Quảng cáo'
            ],

            'Thiết kế' => [
                'Phần mềm thiết kế' => ['Phần Mềm Photoshop', 'Phần Mềm Adobe Illustrator', 'Phần mềm AutoCAD']
            ],

            'Nhiếp ảnh - Dựng phim' => ['Biên tập video', 'Dựng phim', 'Chụp ảnh']

        ];

        foreach ($categories as $key => $category) {
            $category_root_id = DB::table('categories')->insertGetId(
                [
                    'title' => $key,
                    'slug' => Str::slug($key),
                    'parent_id' => NULL
                ]
            );

            foreach ($category as $k => $subcategory) {

                if (is_numeric($k)) {
                    $data_arr =
                        [
                            'title' => $subcategory,
                            'slug' => Str::slug($subcategory),
                            'parent_id' => $category_root_id
                        ];

                    DB::table('categories')->insert($data_arr);
                } else {
                    $data_arr =
                        [
                            'title' => $k,
                            'slug' => Str::slug($k),
                            'parent_id' => $category_root_id
                        ];
                    $subcategory_id = DB::table('categories')->insertGetId($data_arr);

                    foreach ($subcategory as $topic) {
                        $slugArr = ['C#' => 'c-sharp', 'C++' => 'c-plus-plus'];

                        DB::table('categories')->insert(
                            [
                                'title' => $topic,
                                'slug' =>
                                array_key_exists($topic, $slugArr) ? $slugArr[$topic] : Str::slug($topic),
                                'parent_id' => $subcategory_id
                            ]
                        );
                    }
                }
            }
        }
    }
}

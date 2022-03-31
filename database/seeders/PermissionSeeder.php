<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permission')->insert([
            'title' => 'Chỉnh sửa bài viết',
            'name' => 'edit-post'

        ]);  //1
        DB::table('permission')->insert([
            'title' => 'Tạo bài viết',
            'name' => 'create-post'
        ]); //2
        DB::table('permission')->insert([
            'title' => 'Xóa bài viết',
            'name' => 'delete-post'
        ]); //3
        DB::table('permission')->insert([
            'title' => 'Bình luận',
            'name' => 'comment-post',
        ]); //4
        DB::table('permission')->insert([
            'title' => 'User đánh giá',
            'name' => 'user-rating'
        ]); //5
        DB::table('permission')->insert([
            'title' => 'Chỉnh sửa thông tin cá nhân',
            'name' => 'profile'
        ]); //6
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('coupon')->insert([
            [
                'id' => Str::uuid()->toString(),
                'type' => 'Free',
                'description' => 'Tạo mã giảm giá với thời gian khuyến mại giới hạn, cho phép tối đa 1000 học viên ghi danh miễn phí vào khóa học của bạn.',
                'expiration' => 'Đạt 1000 học viên hoặc 5 ngày sau khi kích hoạt mã.',
                'limited_time' => 5,
                'enrollment_limit' => 1000
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'Custom price',
                'description' => 'Tạo mã giảm giá trong khoảng giá cho phép có thời gian khuyến mại giới hạn. Mã giảm giá này không giới hạn người sử dụng.',
                'expiration' => '31 ngày sau khi kích hoạt mã.',
                'limited_time' => 31,
                'enrollment_limit' => 0
            ],
        ]);
    }
}

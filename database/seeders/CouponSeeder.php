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
                'type' => 'FREE',
                'label' => 'Miễn phí',
                'description' => 'Tối đa 1000 học viên ghi danh.',
                'expiration' => 'Hết hạn: 5 ngày sau khi kích hoạt mã.',
                'limited_time' => 5,
                'enrollment_limit' => 1000
            ],
            [
                'id' => Str::uuid()->toString(),
                'type' => 'CUSTOM_PRICE',
                'label' => 'Tùy chỉnh giá',
                'description' => 'Không giới hạn.',
                'expiration' => 'Hết hạn: 31 ngày sau khi kích hoạt mã.',
                'limited_time' => 31,
                'enrollment_limit' => 0
            ],
        ]);
    }
}

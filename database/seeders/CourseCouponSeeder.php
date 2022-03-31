<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseCouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coupon_id = DB::table('coupon')
            ->where('type', 'Custom price')
            ->first()
            ->id;

        for ($i = 1; $i <= 10; $i++) {
            $course = DB::table('course')->where('id', $i)->first(['price_id']);
            $price = DB::table('price')->where('id', $course->price_id)->first();

            DB::table('course_coupon')->insert(
                [
                    'code' => 'KEEPLEARNING',
                    'course_id' => $i,
                    'coupon_id' => $coupon_id,
                    'status' => 1,
                    'discount_price' => $price->price - 3,
                    'expires' => Carbon::now()->addDays(31)
                ]
            );
        }
    }
}

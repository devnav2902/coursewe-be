<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cart_type')->insert(
            [
                [
                    'name' => 'Giỏ hàng',
                    'type' => 'cart'
                ],
                [
                    'name' => 'Thanh toán sau',
                    'type' => 'saved_for_later'
                ],
                [
                    'name' => 'Yêu thích',
                    'type' => 'wishlist'
                ],
            ]
        );
    }
}

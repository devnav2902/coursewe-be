<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PriceSeeder::class,
            CategorySeeder::class,
            InstructionallevelSeeder::class,
            CourseSeeder::class,
            NotificationEntitySeeder::class,
            CouponSeeder::class,
            CourseCouponSeeder::class,
            RegistrationSeeder::class,
            RatingSeeder::class,
            CartTypeSeeder::class,
            CartSeeder::class
        ]);
    }
}

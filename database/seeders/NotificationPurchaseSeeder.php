<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $course_bill = DB::table('course_bill')->get();
        $purchased_entity = DB::table('notification_entity')
            ->where('type', 'purchased')
            ->first();

        foreach ($course_bill as $value) {
            $notification_id = DB::table('notification')->insertGetId(
                ['notification_entity_id' => $purchased_entity->id]
            );

            DB::table('notification_purchase')->insert(
                [
                    'course_bill_id' => $value->id,
                    'notification_id' => $notification_id
                ]
            );
        }
    }
}

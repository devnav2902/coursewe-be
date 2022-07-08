<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QualityReviewTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = DB::table('users')
            ->where('id', '>=', 43)
            ->get(['id']);

        $categories = DB::table('categories')->get(['category_id']);

        for ($j = 0; $j < count($users); $j++) {
            $random = random_int(0, count($categories) - 1);
            $limit = 5;

            for ($i = 0; $i < count($categories); $i++) {
                if ($limit > 0) {
                    if ($random < count($categories)) {
                        DB::table('quality_review_team')->insert([
                            'category_id' => $categories[$random]->category_id,
                            'user_id' => $users[$j]->id,
                        ]);
                        $random++;
                        $limit--;
                    }
                }
            }
        }
    }
}

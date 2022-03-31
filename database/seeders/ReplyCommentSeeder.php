<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

include_once __DIR__ . '/RandomDataSeeder/comment.php';
class ReplyCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reply = comment();
        for ($i = 1; $i <= 200; $i++) {

            $replyRandom = $reply[random_int(0, count($reply) - 1)];
            DB::table('reply_comment')->insert([
                'comment_id' => random_int(1, 200),
                'author_id' => random_int(1, 8),
                'content' => $replyRandom,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification_entity')->insert(
            [
                [
                    'type' => 'approved',
                    'text_start' => 'Xin chúc mừng! Khóa học',
                    'text_end' => 'đã được xét duyệt. Từ bây giờ, học viên có thể ghi danh vào khóa học của bạn.'
                ],
                [
                    'type' => 'unapproved',
                    'text_start' => 'Khóa học',
                    'text_end' => 'cần chỉnh sửa thêm trước khi học viên có thể ghi danh vào khóa học của bạn.'
                ],
                [
                    'type' => 'submit_for_review',
                    'text_start' => 'Khóa học',
                    'text_end' => 'đã yêu cầu xét duyệt thành công! Chúng tôi sẽ xem xét khóa học và thông báo cho bạn ngay khi có kết quả.'
                ],
                [
                    'type' => 'purchased',
                    'text_start' => 'Bạn đã mua thành công khóa học',
                    'text_end' => 'cùng học và đánh giá chất lượng khóa học nhé!'
                ],
                [
                    'type' => 'quality_review',
                    'text_start' => 'Người dùng đã gửi yêu cầu xét duyệt khóa học',
                    'text_end' => ''
                ],
            ]
        );
    }
}

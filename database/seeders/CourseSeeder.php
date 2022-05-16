<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



include_once __DIR__ . '/RandomDataSeeder/title.php';
include_once __DIR__ . '/RandomDataSeeder/thumbnail.php';
include_once __DIR__ . '/RandomDataSeeder/course.php';
include_once __DIR__ . '/RandomDataSeeder/course-outcome.php';

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $thumbnail = thumbnail();

        $courses = course();
        $sections = [
            [
                'title' => 'Giới thiệu',
                'lecture' => 'Lời giới thiệu, cám ơn và hướng dẫn quan trọng cần nắm'
            ],
            [
                'title' => 'Bước đầu làm quen',
                'lecture' => 'Một số nội dung cần nắm'
            ],
            [
                'title' => 'Tổng kết khóa học',
                'lecture' => 'Lời cảm ơn'
            ]
        ];
        $course_subtitle = "Khóa học dành cho những bạn mới bắt đầu";
        $description = "<div class='study-benefit__list'><div class='study-benefit__item'><i class='fa fa-check icon'></i>Lượng kiến thức khổng lồ về Javascript được phân bổ hợp lý theo từng video một</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Có kiến thức nền tảng tốt về Javascript để có thể học cao lên sau này</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Làm được những chức năng, hiệu ứng trên web từ a-z</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Biết thế nào là API, xử lý dữ liệu, xử lý logic, tư duy giải quyết vấn đề tốt</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Mỗi video giải thích kỹ càng và chi tiết, cũng như thời lượng video ngắn giúp bạn học hiệu quả hơn</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Được chia sẻ nhiều tips, tricks, các nguồn học bổ ích để cải thiện trình độ</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Và rất nhiều kiến thức khác đang chờ đợi các bạn trong khoá học này</div></div>";

        for ($i = 0; $i < 100; $i++) {
            $course = $courses[random_int(1, count($courses) - 1)];
            $author_id = random_int(1, 10);
            $levelId = random_int(0, 3);
            $id = db::table('course')->insertGetId(
                [
                    'instructional_level_id' => $levelId,
                    'subtitle' => $course_subtitle,
                    'author_id' => $author_id,
                    'price_id' => random_int(1, 19),
                    'title' => $course['title'],
                    'description' => $description,
                    'slug' => Str::slug($course['title'] . '-' . random_int(1, 1000)),
                    'video_demo' => 'lesson/test.mp4',
                    'isPublished' => 1,
                    'thumbnail' => $thumbnail[random_int(1, count($thumbnail) - 1)]
                ]
            );

            foreach ($course['category'] as $cat) {
                $cat_id = db::table('categories')
                    ->where('slug', 'LIKE', '%' . $cat . '%')
                    ->first('category_id');

                if ($cat_id) {
                    db::table('categories_course')
                        ->insert(
                            [
                                'course_id' => $id,
                                'category_id' => $cat_id->category_id
                            ]
                        );
                }
            }

            foreach ($sections as $key => $section) {
                $sec_id = db::table('sections')->insertGetId(
                    [
                        'course_id' => $id,
                        'title' => $section['title'],
                        'order' => $key + 1
                    ]
                );


                db::table('lectures')->insertGetId([
                    'section_id' => $sec_id,
                    'order' => $key + 1,
                    'title' => $section['lecture'],
                    'src' => 'lesson/test.mp4',
                    'playtime_seconds' => '30.2012',
                    'playtime_string' => '30:02'
                ]);
            }

            DB::table('course_outcome')->insert(courseOutcome($id));
        }
    }
}

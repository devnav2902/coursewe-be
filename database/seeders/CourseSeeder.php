<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



include_once __DIR__ . '/RandomDataSeeder/title.php';
include_once __DIR__ . '/RandomDataSeeder/thumbnail.php';

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
        $courses = [
            [
                'title' => 'KHÓA HỌC ANGULAR & TYPESCRIPT (FRONT END)',
                'category' => ['angularjs', 'typescript'],
            ],
            [
                'title' => 'Khóa học NodeJS căn bản',
                'category' => ['nodejs'],
            ],
            [
                'title' => 'Khóa học Lập trình Angular',
                'category' => ['angularjs'],
            ],
            [
                'title' => 'Khóa học vue js master',
                'category' => ['vuejs'],
            ],
            [
                'title' => 'Lập trình Front-End VueJS',
                'category' => ['vuejs'],
            ],
            [
                'title' => 'Khoá học Lập trình web Vue.js căn bản',
                'category' => ['vuejs'],
            ],
            [
                'title' => 'Khóa học lập trình NodeJS',
                'category' => ['nodejs'],
            ],
            [
                'title' => 'KHÓA HỌC LẬP TRÌNH PHP NÂNG CAO',
                'category' => ['php'],
            ],
            [
                'title' => 'Khóa Học Lập Trình Laravel Từ A Đến Z',
                'category' => ['laravel'],
            ],
            [
                'title' => 'Khóa học lập trình miễn phí Vue 3 & NestJS',
                'category' => ['vuejs', 'nestjs'],
            ],
            [
                'title' => 'Lập trình web nâng cao với LARAVEL FRAMEWORK',
                'category' => ['laravel'],
            ],
            [
                'title' => 'Khóa học Lập trình web HTML5, CSS3, jQuery, Bootstrap',
                'category' => ['html', 'css', 'jquery', 'bootstrap'],
            ],
            [
                'title' => 'Khóa học HTML và CSS cơ bản',
                'category' => ['html', 'css'],
            ],
            [
                'title' => 'ReactJS cho người mới bắt đầu',
                'category' => ['reactjs'],
            ],
            [
                'title' => 'Học Javascript với 9 chuyên đề từ dễ đến khó',
                'category' => ['javascript'],
            ],
            [
                'title' => 'JavaScript Cơ Bản',
                'category' => ['javascript'],
            ],
        ];
        $sections = [
            [
                'title' => 'Giới thiệu về ',
                'lecture' => 'Lời giới thiệu, cám ơn và hướng dẫn quan trọng cần nắm'
            ],
            [
                'title' => 'Bước đầu làm quen với ',
                'lecture' => 'Một số nội dung cần nắm'
            ],
        ];
        $course_subtitle = "Good course for everyone";
        $description = "<div class='study-benefit__list'><div class='study-benefit__item'><i class='fa fa-check icon'></i>Lượng kiến thức khổng lồ về Javascript được phân bổ hợp lý theo từng video một</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Có kiến thức nền tảng tốt về Javascript để có thể học cao lên sau này</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Làm được những chức năng, hiệu ứng trên web từ a-z</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Biết thế nào là API, xử lý dữ liệu, xử lý logic, tư duy giải quyết vấn đề tốt</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Mỗi video giải thích kỹ càng và chi tiết, cũng như thời lượng video ngắn giúp bạn học hiệu quả hơn</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Được chia sẻ nhiều tips, tricks, các nguồn học bổ ích để cải thiện trình độ</div><div class='study-benefit__item'><i class='fa fa-check icon'></i>Và rất nhiều kiến thức khác đang chờ đợi các bạn trong khoá học này</div></div>";

        foreach ($courses as $course) {
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
                    'slug' => Str::slug($course['title']),
                    'video_demo' => 'lesson/test.mp4',
                    'isPublished' => 1,
                    'thumbnail' => $thumbnail[random_int(1, count($thumbnail) - 1)]
                ]
            );

            DB::table('bio')->insert(
                [
                    'headline' =>
                    'Học và làm việc tại trường Giao Thông Vận Tải TP.Hồ Chí Minh',
                    'bio' => ' Mình là một Frontend Developer, ngoài ra mình còn viết blog và làm youtube nữa. Mình thích chia sẻ kiến thức tới cộng đồng, giúp đỡ các bạn theo ngành này có thể học hỏi nâng cao trình độ hơn mỗi ngày. Hi vọng khoá học của mình sẽ giúp các bạn cải thiện được trình độ nhiều nhất có thể.',
                    'youtube' => 'https://www.youtube.com/channel/UCLphTurxkwnUZpOAPXSjw0g',
                    'facebook' => 'https://www.facebook.com',
                    'linkedin' => 'https://www.linkedin.com',
                    'user_id' => $author_id
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

                $category = '';
                foreach ($course['category'] as $key => $value) {
                    $key !== count($course['category']) - 1
                        ? $category .= $value . ", "
                        : $category .= $value;
                }
                $sec_id = db::table('sections')->insertGetId(
                    [
                        'course_id' => $id,
                        'title' => $section['title'] . " " . $category,
                        'order' => $key + 1
                    ]
                );


                db::table('lectures')->insertGetId([
                    'section_id' => $sec_id,
                    'order' => $key + 1,
                    'title' => $section['lecture'],
                    'src' => 'lesson/test.mp4'
                ]);
            }
        }
    }
}

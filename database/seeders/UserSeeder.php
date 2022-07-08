<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

include_once __DIR__ . '/RandomDataSeeder/admin.php';
include_once __DIR__ . '/RandomDataSeeder/user.php';

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin = admin();

        for ($i = 0; $i <= count($admin) - 1; $i++) {
            DB::table('users')
                ->insertGetId($this->createUser($admin[$i], 1));
        }

        for ($i = 1; $i <= 40; $i++) {
            $user = user(random_int(0, 1));

            DB::table('users')
                ->insertGetId($this->createUser($user, 2, $user['gender']));
        }

        for ($i = 1; $i <= 25; $i++) {
            $user = user(random_int(0, 1));

            DB::table('users')
                ->insertGetId($this->createUser($user, 3, $user['gender']));
        }
    }

    function createUser($user, $role_id, $gender = 'male')
    {
        $fullname = $user['lastName'] . ' ' . $user['firstName'];
        $email =
            strtolower(Str::slug($fullname, ''))  . random_int(1, 1000) . '@gmail.com';


        $data = $gender == 'male' ? 'men' : 'women';


        return [
            'fullname' => $fullname,
            'slug' => Str::slug($fullname, ''),
            'avatar' => !empty($user['avatar']) ? $user['avatar'] : 'https://randomuser.me/api/portraits/' . $data . '/' . random_int(1, 50) . '.jpg',
            'role_id' => $role_id,
            'password' => Hash::make('123'),
            'email' => $email,
            'headline' =>
            'Học và làm việc tại trường Giao Thông Vận Tải TP.Hồ Chí Minh',
            'bio' => ' Mình là một Frontend Developer, ngoài ra mình còn viết blog và làm youtube nữa. Mình thích chia sẻ kiến thức tới cộng đồng, giúp đỡ các bạn theo ngành này có thể học hỏi nâng cao trình độ hơn mỗi ngày. Hi vọng khoá học của mình sẽ giúp các bạn cải thiện được trình độ nhiều nhất có thể.',
            'youtube' => 'UCLphTurxkwnUZpOAPXSjw0g',
            'facebook' => 'devnav2902',
            'linkedin' => 'devnav2902',
        ];
    }
}

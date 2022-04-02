<?php
function user($key = 0)
{
    $infoUser = [
        [
            'firstName'
            => ['Tú', 'Hoàng', 'An', 'Long', 'Minh', 'Hùng', 'Thành', 'Trường', 'Phụng', 'Tùng', 'Thiên', 'Đạt', 'Anh', 'Hải', 'Sơn', 'Lâm', 'Đức'],
            'lastName'
            =>
            ['Nguyễn Văn', 'Trần Thanh', 'Trần Văn', 'Nguyễn Anh', 'Đặng Hoàng', 'Đặng Văn', 'Trần Tiến', 'Lê Văn', 'Lê Trường', 'Lê Thanh', 'Trần Nhật']
        ],
        [
            'firstName'
            => ['Nhi', 'Vy', 'Hà', 'Hoài', 'Thương', 'Thanh', 'Thi', 'Mi', 'Tú', 'Lan', 'Trang', 'Phượng', 'Hằng', 'Mai'],
            'lastName'
            =>
            ['Nguyễn Thảo', 'Lê Trang', 'Chu Thị Minh', 'Trần Thanh', 'Ngô Thị', 'Nguyễn Yến', 'Nguyễn Thị Thu', 'Lê Yên', 'Lê Hải', 'Nguyễn Ngọc', 'Nguyễn Thu']
        ],
    ];


    $info = $infoUser[0];
    if ($key == 1) $info = $infoUser[$key];

    return [
        'gender' => $key == 0 ? 'male' : 'female',
        'firstName' =>
        $info['firstName'][random_int(0, count($info['firstName']) - 1)],
        'lastName' =>
        $info['lastName'][random_int(0, count($info['lastName']) - 1)],
        'role' => 'user'
    ];
}

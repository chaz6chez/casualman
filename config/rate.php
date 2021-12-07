<?php
/*
    路由名 => [
        'qos' => 每秒频次(int)
        'capacity' => 容量(int)
    ]
*/
return [
    \CasualMan\Common\Internal\RateLimit\AbstractRate::BASE_KEY => [
        'qos' => 1000,
        'capacity' => 5000,
    ],
];

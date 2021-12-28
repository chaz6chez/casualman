<?php
/*
    路由名 => [
        'qos'      => 频次(int),
        'capacity' => 容量(int),
        'interval' => 间隔(int)
    ]
*/
return [
    \CasualMan\Common\Internal\RateLimit\AbstractRate::BASE_KEY => [
        'qos'      => 100,
        'capacity' => 500,
        'interval' => 1
    ],
];

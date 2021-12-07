<?php
return [
    'default' => [
        'host'       => E('redis.host'),
        'port'       => (int)E('redis.port'),
        'password'   => E('redis.password'),
        'select'     => E('redis.database'),
        'timeout'    => 2.5,   # 秒为单位
        'expire'     => 0,
        'persistent' => false, # 持久化
        'prefix'     => E('redis.prefix'),
    ],
];
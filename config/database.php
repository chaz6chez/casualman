<?php

return [
    'mysql' => [
        'demo' => [
            'driver'   => 'mysql',
            'host'     => E('mysql.host'),
            'port'     => E('mysql.port'),
            'username' => E('mysql.username'),
            'password' => E('mysql.password'),
            'charset'  => 'utf8mb4',
            'dbname'   => 'demo',
            'option'   => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'error'    => PDO::ERRMODE_EXCEPTION,
            'prefix'        => E('mysql.prefix'),
            'slave' => [
                'driver'   => 'mysql',
                'host'     => E('mysql.host'),
                'port'     => E('mysql.port'),
                'username' => E('mysql.username'),
                'password' => E('mysql.password'),
                'charset'  => 'utf8mb4',
                'dbname'   => 'demo',
                'option'   => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'error'    => PDO::ERRMODE_EXCEPTION,
                'prefix'        => E('mysql.prefix'),
            ]
        ]
    ]
];
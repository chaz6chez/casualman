<?php
return [
    'mysql' => [
        'demo' => [
            'driver'   => 'mysql',
            'host'     => E('mysql.host'),
            'port'     => E('mysql.port'),
            'dsn'      => '',
            'username' => E('mysql.username'),
            'password' => E('mysql.password'),
            'charset'  => 'utf8mb4',
            'dbname'   => 'demo',
            'option'   => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'prefix'   => E('mysql.prefix'),
            'error'    => PDO::ERRMODE_EXCEPTION,
            'slave'    => [
                'driver'   => 'mysql',
                'host'     => E('mysql.host'),
                'port'     => E('mysql.port'),
                'dsn'      => '',
                'username' => E('mysql.username'),
                'password' => E('mysql.password'),
                'charset'  => 'utf8mb4',
                'dbname'   => 'demo',
                'option'   => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'prefix'   => E('mysql.prefix'),
                'error'    => PDO::ERRMODE_EXCEPTION,
            ]
        ],
    ],
    'mongodb' => [
        '3y_clearing' => [
            'host'     => E('mongodb.host'),
            'port'     => E('mongodb.port'),
            'username' => E('mongodb.username'),
            'password' => E('mongodb.port'),
        ]
    ]
];
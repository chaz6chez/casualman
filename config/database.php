<?php

return [
    'mysql' => [
        'casualman' => [
            'database_type' => 'mysql',
            'server'        => E('mysql.host'),
            'username'      => E('mysql.username'),
            'password'      => E('mysql.password'),
            'database_file' => '',
            'port'          => E('mysql.port'),
            'charset'       => 'utf8mb4',
            'database_name' => 'casualman',
            'option'        => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'prefix'        => E('mysql.prefix'),
            'slave' => [
                'database_type' => 'mysql',
                'server'        => E('mysql.host'),
                'username'      => E('mysql.username'),
                'password'      => E('mysql.password'),
                'database_file' => '',
                'port'          => E('mysql.port'),
                'charset'       => 'utf8mb4',
                'database_name' => 'casualman',
                'option'        => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'prefix'        => E('mysql.prefix'),
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
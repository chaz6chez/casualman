<?php

return [
    'mysql' => [
        '3y_clearing' => [
            'database_type' => 'mysql',
            'server'        => E('mysql_demo.host'),
            'username'      => E('mysql_demo.username'),
            'password'      => E('mysql_demo.password'),
            'database_file' => '',
            'port'          => E('mysql_demo.port'),
            'charset'       => 'utf8mb4',
            'database_name' => '3y_clearing',
            'option'        => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'prefix'        => E('mysql_demo.prefix'),
            'slave' => [
                'database_type' => 'mysql',
                'server'        => E('mysql_demo.host'),
                'username'      => E('mysql_demo.username'),
                'password'      => E('mysql_demo.password'),
                'database_file' => '',
                'port'          => E('mysql_demo.port'),
                'charset'       => 'utf8mb4',
                'database_name' => '3y_clearing',
                'option'        => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'prefix'        => E('mysql_demo.prefix'),
            ]
        ],
    ],
    'mongodb' => [
        '3y_clearing' => [
            'host'     => E('mongodb_demo.host'),
            'port'     => E('mongodb_demo.port'),
            'username' => E('mongodb_demo.username'),
            'password' => E('mongodb_demo.port'),
        ]
    ]
];
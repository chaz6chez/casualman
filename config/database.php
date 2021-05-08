<?php

return [
    'mysql' => [
        '3y_clearing' => [
            'database_type' => 'mysql',
            'server'        => E('mysql_clearing.host'),
            'username'      => E('mysql_clearing.username'),
            'password'      => E('mysql_clearing.password'),
            'database_file' => '',
            'port'          => E('mysql_clearing.port'),
            'charset'       => 'utf8mb4',
            'database_name' => E('mysql_clearing.database'),
            'option'        => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'prefix'        => E('mysql_clearing.prefix'),
            'slave' => [
                'database_type' => 'mysql',
                'server'        => E('mysql_clearing.host'),
                'username'      => E('mysql_clearing.username'),
                'password'      => E('mysql_clearing.password'),
                'database_file' => '',
                'port'          => E('mysql_clearing.port'),
                'charset'       => 'utf8mb4',
                'database_name' => E('mysql_clearing.database'),
                'option'        => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'prefix'        => E('mysql_clearing.prefix'),
            ]
        ],
        '3y_history' => [
            'database_type' => 'mysql',
            'server'        => E('mysql_history.host'),
            'username'      => E('mysql_history.username'),
            'password'      => E('mysql_history.password'),
            'database_file' => '',
            'port'          => E('mysql_history.port'),
            'charset'       => 'utf8mb4',
            'database_name' => E('mysql_history.database'),
            'option'        => [
                PDO::ATTR_PERSISTENT       => true, # 长连接
                PDO::ATTR_TIMEOUT          => 2,
                PDO::ATTR_EMULATE_PREPARES => false
            ],
            'prefix'        => E('mysql_history.prefix'),
            'slave' => [
                'database_type' => 'mysql',
                'server'        => E('mysql_history.host'),
                'username'      => E('mysql_history.username'),
                'password'      => E('mysql_history.password'),
                'database_file' => '',
                'port'          => E('mysql_history.port'),
                'charset'       => 'utf8mb4',
                'database_name' => E('mysql_history.database'),
                'option'        => [
                    PDO::ATTR_PERSISTENT       => true, # 长连接
                    PDO::ATTR_TIMEOUT          => 2,
                    PDO::ATTR_EMULATE_PREPARES => false
                ],
                'prefix'        => E('mysql_history.prefix'),
            ]
        ],
    ],
    'mongodb' => [
        '3y_clearing' => [
            'host'     => E('mongodb_clearing.host'),
            'port'     => E('mongodb_clearing.port'),
            'username' => E('mongodb_clearing.username'),
            'password' => E('mongodb_clearing.port'),
        ]
    ]
];
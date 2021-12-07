<?php
return [
    'rpc_server' => [
        'handler'    => \CasualMan\Common\Process\RpcServer::class,
        'listen'     => 'JsonRpc2://[::]:5454',
        'count'      => DEBUG ? 2 : 16,
        'reuse_port' => true,
        'reloadable' => true,
    ],
    'http_server' => [
        'handler'    => \CasualMan\Common\Process\HttpServer::class,
        'listen'     => 'Http://[::]:6464',
        'count'      => DEBUG ? 2 : 4,
        'reuse_port' => true,
        'reloadable' => true,
        'transfer'   => '127.0.0.1:5454'
    ]
];
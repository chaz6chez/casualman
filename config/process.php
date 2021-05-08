<?php
return [
//    'name' => [
//        'handler'    => \Internal\Kernel\AbstractProcess::class,
//        'listen'     => 'jsonRpc://0.0.0.0:5454',
//        'count'      => 4,
//        'reuse_port' => true,
//        'reloadable' => true
//    ],
    'rpc_server' => [
        'handler'    => \CasualMan\Common\Process\RpcServer::class,
        'listen'     => 'JsonRpc2://0.0.0.0:5454',
        'count'      => 4,
        'reuse_port' => true,
        'reloadable' => true
    ],
];
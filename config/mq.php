<?php
return [
    'amqp' => [
        'host'               => E('rabbit_mq.host'),
        'vhost'              => E('rabbit_mq.vhost', '/'),
        'port'               => E('rabbit_mq.port'),
        'username'           => E('rabbit_mq.username'),
        'password'           => E('rabbit_mq.password'),
        'timeout'            => 5,
        'heartbeat'          => 60,
        'heartbeat_callback' => function(){

        }
    ]
];
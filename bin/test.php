<?php
declare(strict_types=1);
function getCurrentTime() {
    list ($msec, $sec) = explode(" ", microtime());
    return (float)$msec + (float)$sec;
}

define('HOST', 'tcp://127.0.0.1:5454');
ini_set('date.timezone','Asia/Shanghai');
define('ROOT_PATH'  , dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

$start = getCurrentTime();
$client = \CasualMan\Common\Internal\RpcClient::instance([HOST]);
$v = $client->call('@demo',
    [
        'unique_tag'  => 'test-a',
        'beneficiary' => 'test-a',
    ],
    \CasualMan\Common\Internal\RpcClient::uuid()
);

dump($v);
dump('test:' . (getCurrentTime() - $start));



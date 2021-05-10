<?php
declare(strict_types=1);

ini_set('date.timezone','Asia/Shanghai');
define('ROOT_PATH'  , dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

use Kernel\ApplicationFactory;

try{
    (new ApplicationFactory())('Rpc-server','0.0.1')->run();
}catch(Throwable $throwable){
    exit($throwable->getMessage());
}
<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Controller;

use CasualMan\Clearing\Service\DemoService;

class Demo {
    public function demo() : string {
        $res = DemoService::instance()->Demo();
        if(!$res->hasError()){
            dump($res->getData());
        }
        return 'hello world!';
    }
}
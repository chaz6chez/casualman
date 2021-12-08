<?php
declare(strict_types=1);

namespace CasualMan\Application\Controller;


use CasualMan\Application\Service\DemoService;

class RpcDemo extends RpcController {

    public function Timestamp() :array
    {
        return $this->success(['time' => microtime(true)]);
    }

    public function Index() :array
    {
        $res = DemoService::instance()->test();
        if($res->hasError()){
            return $this->error($res->getMessage(), $res->getCode());
        }
        return $this->success($res->getData());
    }
}
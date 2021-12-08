<?php
namespace CasualMan\Application\Service;

use Kernel\Utils\Service;
use Kernel\Utils\Response;

class DemoService extends Service {

    public function test() : Response
    {
        return $this->response()->success('this is demo service');
    }
}
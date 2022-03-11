<?php
declare(strict_types=1);

namespace CasualMan\Application\Service;

use Kernel\Utils\Response;

class DemoService extends BaseService
{

    public function test() : Response
    {
        return $this->response()->success('this is demo service');
    }
}
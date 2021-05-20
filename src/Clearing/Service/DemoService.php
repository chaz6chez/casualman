<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Service;

use Kernel\Utils\Response;
use Kernel\Utils\Service;

class DemoService extends Service {
    public function Demo() : Response{
        return $this->response()->success(rand(100,999));
    }
}
<?php
declare(strict_types=1);

namespace CasualMan\Demo\Middleware;

use Kernel\Protocols\MiddlewareInterface;

class DemoMid implements MiddlewareInterface {

    public function process(\Closure $next, ...$param)
    {
        dump(__CLASS__ . '::before');
        $res = callback($next, ...$param);
        dump(__CLASS__ . '::after');
        return $res;
    }
}
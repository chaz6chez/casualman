<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Middleware;

use Kernel\Protocols\MiddlewareInterface;

class DemoMid implements MiddlewareInterface {

    public function process(\Closure $next, ...$param)
    {
        var_dump('before' . PHP_EOL);
        $res = callback($next, ...$param);
        var_dump('after' . PHP_EOL);
        return $res;
    }
}
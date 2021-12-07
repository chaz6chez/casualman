<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Middleware;

use CasualMan\Clearing\Controller\Controller;
use CasualMan\Common\Internal\AbstractController;
use Utils\Context;
use Database\Driver;
use Kernel\Protocols\MiddlewareInterface;

class DebugSQLMiddleware implements MiddlewareInterface {
    public function process(\Closure $next, ...$param)
    {
        if(G($param, AbstractController::class)){
            debug_helper(function (){
                if(Driver::$onAfterExec === null){
                    Driver::$onAfterExec = function (Driver $driver){
                        Context::add($driver->last());
                    };
                }
            });
        }
        return $next(...$param);
    }
}
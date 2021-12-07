<?php

declare(strict_types=1);

namespace CasualMan\Clearing\Middleware;

use CasualMan\Clearing\Error\ErrorCode;
use CasualMan\Clearing\RateLimit\ClearingRateLimit;
use CasualMan\Common\Internal\AbstractController;
use Kernel\Protocols\MiddlewareInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function process(\Closure $next, ...$param)
    {
        /** @var AbstractController $controller */
        $controller = G($param, AbstractController::class);
        if($controller){
            if ((new ClearingRateLimit)->limit()) {
                return $next(...$param);
            } else {
                return $controller->error(ErrorCode::INTERFACE_OVERCLOCK);
            }
        }
        return $next(...$param);
    }
}

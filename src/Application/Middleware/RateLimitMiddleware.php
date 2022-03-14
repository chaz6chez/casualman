<?php

declare(strict_types=1);

namespace CasualMan\Application\Middleware;

use CasualMan\Application\Error\ErrorCode;
use CasualMan\Application\RateLimit\ClearingRateLimit;
use CasualMan\Common\AbstractController;
use Kernel\Protocols\MiddlewareInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function process(\Closure $next, ...$param)
    {
        /** @var AbstractController $controller */
        $controller = G($param, AbstractController::class);
        if($controller){
            $limit = make(ClearingRateLimit::class)->limit();
            if ($limit === false) {
                return $controller->error(ErrorCode::INTERFACE_OVERCLOCK);
            } else {
                return $next(...$param);
            }
        }
        return $next(...$param);
    }
}

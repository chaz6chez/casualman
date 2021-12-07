<?php
declare(strict_types=1);

namespace CasualMan\Clearing\Middleware;

use CasualMan\Clearing\Controller\RpcController;
use Kernel\Protocols\MiddlewareInterface;

class ServerErrorCatchMiddleware implements MiddlewareInterface {

    public function process(\Closure $next, ...$param)
    {
        /** @var RpcController $controller */
        $controller = G($param, RpcController::class);
        if($controller){
            $controller->init();
            $callback = $next(...$param);
            if($controller::isServerError($callback)){
                $controller::throwServiceError($callback);
            }
        }else{
            $callback = $next(...$param);
        }
        return $callback;
    }


}
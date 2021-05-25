<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class MethodNotFoundException extends RpcException {

    public function __construct($message = 'Method not found', $code = -32601, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
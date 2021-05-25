<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class InvalidParamsException extends RpcException {

    public function __construct($message = 'Invalid params', $code = -32602, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
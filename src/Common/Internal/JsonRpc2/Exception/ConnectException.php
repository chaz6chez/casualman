<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class ConnectException extends RpcException {

    public function __construct($message = 'Connect failed', $code = -32604, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
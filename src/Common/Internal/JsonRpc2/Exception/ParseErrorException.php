<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class ParseErrorException extends RpcException {

    public function __construct($message = 'Parse error', $code = -32700, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
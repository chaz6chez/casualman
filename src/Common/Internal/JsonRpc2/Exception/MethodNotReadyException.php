<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class MethodNotReadyException extends RpcException {

    /**
     * InternalErrorException constructor.
     * @param string $message
     * @param int $code -32000 to -32099
     * @param Throwable|null $previous
     */
    public function __construct($message , $code = -32003, Throwable $previous = null) {
        parent::__construct("Server error [Method Not Ready: {$message}]", $code, $previous);
    }
}
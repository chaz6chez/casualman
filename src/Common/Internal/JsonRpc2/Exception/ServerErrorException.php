<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class ServerErrorException extends RpcException {

    /**
     * InternalErrorException constructor.
     * @param string $message
     * @param int $code -32000 to -32099
     * @param Throwable|null $previous
     */
    public function __construct($message = 'Server error', $code = -32000, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

<?php
namespace CasualMan\Common\Internal\JsonRpc2\Exception;

use Throwable;

class ServiceErrorException extends RpcException {

    protected $info;

    public function __construct($message = 'Service error', $code = null, $info = [], Throwable $previous = null) {
        $this->info = $info;
        $code = $code ? (int)$code : -32604;
        parent::__construct($message, $code, $previous);
    }

    public function getInfo(){
        return $this->info;
    }
}
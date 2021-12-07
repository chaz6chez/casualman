<?php
namespace CasualMan\Clearing\Error;

class ErrorCode {

    const ILLEGAL_REQUEST      = '0x001|Illegal request.';
    const UNKNOWN_REQUEST      = '0x002|Unknown request.';
    const REQUEST_REJECTED     = '0x003|Request rejected.';
    const SYSTEM_ERROR         = '0x004|System error.';
    const INTERFACE_OVERCLOCK  = '0x005|Interface overclocking.';

    public static function code(string $errorCode) : string
    {
        return (string)explode('|', $errorCode, 2)[0];
    }

    public static function msg(string $errorCode) :string
    {
        return (string)explode('|', $errorCode, 2)[1];
    }

    public static function exception(string $errorCode) :bool
    {
        return boolval(mb_strrpos($errorCode, '0x') !== false);
    }
}
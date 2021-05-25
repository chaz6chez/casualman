<?php
namespace CasualMan\Common\Internal\JsonRpc2\Format;

class ErrorFmt extends BaseFmt {
    /**
     * @var
     * @required true|code cannot be empty:0x000
     */
    public $code;

    /**
     * @var
     * @required true|message cannot be empty:0x000
     */
    public $message;

    /**
     * @var
     */
    public $data;
}
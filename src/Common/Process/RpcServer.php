<?php
declare(strict_types=1);

namespace CasualMan\Common\Process;

use Utils\Context;
use Protocols\JsonRpc2;
use Psr\Log\LoggerInterface;
use Utils\JsonRpc2\Exception\MethodNotFoundException;
use Utils\JsonRpc2\Exception\RpcException;
use Utils\JsonRpc2\Exception\ServerErrorException;
use Utils\JsonRpc2\Exception\ServiceErrorException;
use Utils\JsonRpc2\Format\ErrorFmt;
use Utils\JsonRpc2\Format\JsonFmt;
use Kernel\AbstractProcess;
use Kernel\Protocols\ListenerInterface;
use Kernel\Router;
use Workerman\Connection\TcpConnection;
use \Throwable;
use \Exception;

class RpcServer extends AbstractProcess implements ListenerInterface
{
    /** @var TcpConnection|null */
    protected static $_connection;

    /** @var array|null */
    protected static $_data;

    /** @var RpcException|null */
    protected static $_exception;

    /** @var JsonFmt|null */
    protected static $_jsonFormat;

    /** @var LoggerInterface|null */
    protected $_logger;

    /** @var callable|null */
    protected $_before;

    /** @var callable|null */
    protected $_after;

    public static function connection() : ?TcpConnection
    {
        return self::$_connection;
    }

    public static function jsonFormat() : ?JsonFmt
    {
        return self::$_jsonFormat;
    }

    /**
     * @param bool $trim
     * @return string
     */
    public static function getRaw(bool $trim = true) : string
    {
        return $trim ? trim(JsonRpc2::$buffer) : JsonRpc2::$buffer;
    }

    /**
     * @param string|null $key
     * @param null $default
     * @return object|array|string|int|null
     */
    public static function getData(?string $key = null, $default = null)
    {
        if($key) {
            return isset(self::$_data[$key]) ? self::$_data[$key] : $default;
        }
        return self::$_data;
    }

    public function onStart(...$param): void
    {
        if(!$this->_logger instanceof LoggerInterface){
            /** @var LoggerInterface _logger */
            $this->_logger = C('logger');
        }
    }

    public function onStop(...$param): void
    {
        $this->_logger = null;
    }

    public function onReload(...$param): void {}

    public function onBufferDrain(...$params) : void
    {
        $this->_logger->notice(__METHOD__,$params ?? []);
    }

    public function onBufferFull(...$params) : void
    {
        $this->_logger->notice(__METHOD__,$params ?? []);
    }

    public function onClose(...$params) : void
    {
        $this->_clean();
    }

    public function onConnect(...$params) : void {}

    public function onError(...$params) : void {
        $this->_logger->notice(__METHOD__,$params ?? []);
    }

    public function onMessage(...$params) : void
    {
        $this->_init(...$params);

        if(self::$_exception instanceof RpcException){
            $this->_error(self::$_exception);
            return;
        }
        if(self::$_exception instanceof Throwable){
            $this->_error(new ServerErrorException(), self::$_exception);
            return;
        }
        try {
            if(!self::$_jsonFormat->result = Router::dispatch(
                self::$_jsonFormat->id ? 'normal' : 'notice',
                (string)self::$_jsonFormat->method,null
            )){
                $this->_error(new ServerErrorException(), '500 SERVER ERROR' );
                return;
            }
            $this->_success(self::$_jsonFormat->id ?
                self::$_jsonFormat
                    ->scene(self::$_jsonFormat::TYPE_RESPONSE)
                    ->filter(STRUCT_FILTER_KEY_REVERSE, STRUCT_FILTER_NULL)
                    ->output() :
                []
            );
            return;
        }catch (Exception $exception){
            if($exception->getCode() === 404){
                $this->_error(new MethodNotFoundException(), '404 NOT FOUND');
                return;
            }
            $this->_error(new ServerErrorException(), $exception->getPrevious() ?? $exception);
            return;
        }
    }

    protected function _init(...$params) : void
    {
        [self::$_connection, self::$_data] = $params;
        [self::$_exception, $buffer] = JsonRpc2::request((array)self::$_data);
        self::$_jsonFormat = JsonFmt::factory((array)$buffer);

        if(is_callable($this->_before)){
            ($this->_before)($this);
        }
    }

    protected function _clean() : void {
        [self::$_exception, $buffer] = [self::$_connection, self::$_data] = [null, null];
        self::$_jsonFormat = null;
    }

    protected function _error(RpcException $exception, $info = null) : void {
        $errorFmt = ErrorFmt::factory();
        $errorFmt->code    = $exception->getCode();
        $errorFmt->message = $exception->getMessage();
        if($info instanceof Throwable){
            $info = DEBUG ? [
                '_code'    => $info->getCode(),
                '_message' => $info->getMessage(),
                '_file'    => $info->getFile() . '(' .$info->getLine(). ')',
                '_info'    => ($previous = $info->getPrevious()) ? $previous->getMessage() : '',
            ] : [
                '_code'    => $info->getCode(),
                '_message' => $info->getMessage(),
            ];
        }
        $errorFmt->data = $info ?? null;
        self::$_jsonFormat->error = $errorFmt->filter(STRUCT_FILTER_NULL,STRUCT_FILTER_EMPTY)->output();

        $this->_response(self::$_jsonFormat
            ->scene(self::$_jsonFormat::TYPE_RESPONSE)
            ->filter(STRUCT_FILTER_KEY_REVERSE,STRUCT_FILTER_NULL)
            ->output());
    }
    
    protected function _success(array $buffer) : void
    {
        $this->_response($buffer);
    }

    protected function _response(array $buffer) : void
    {
        if(DEBUG){
            if(isset($buffer['result'])){
                $buffer['result']['_sql'] = Context::context();
            }
            if(isset($buffer['error']['data'])){
                if(!is_array($buffer['error']['data'])){
                    $buffer['error']['data'] = [$buffer['error']['data']];
                }
                $buffer['error']['data']['_sql'] = Context::context();
            }
        }
        self::connection()->send($buffer);

        if(is_callable($this->_after)){
            ($this->_after)($this);
        }
    }

}
<?php
declare(strict_types=1);

namespace CasualMan\Common\Process;

use JsonRpcServer\Exception\MethodNotFoundException;
use JsonRpcServer\Exception\RpcException;
use JsonRpcServer\Exception\ServerErrorException;
use JsonRpcServer\Exception\ServiceErrorException;
use JsonRpcServer\Format\ErrorFmt;
use JsonRpcServer\Format\JsonFmt;
use Kernel\AbstractProcess;
use Kernel\Protocols\ListenerInterface;
use Kernel\Router;
use Workerman\Connection\TcpConnection;

class RpcServer extends AbstractProcess implements ListenerInterface{
    /**
     * @var TcpConnection
     */
    protected static $_connection;
    /**
     * @var array
     */
    protected static $_params;
    /**
     * @var RpcException
     */
    protected static $_exception;
    /**
     * @var JsonFmt
     */
    protected static $_jsonFormat;
    protected static $_debug = false;

    public static function connection() : TcpConnection{
        return self::$_connection;
    }
    public static function params() : array {
        return self::$_params;
    }
    public static function debug(bool $debug = false){
        self::$_debug = $debug;
    }

    public function onStart(...$param): void {} //TODO 资源初始化
    public function onReload(...$param): void {}
    public function onStop(...$param): void {} //TODO 资源释放
    public function onBufferDrain(...$params) : void {} //TODO 记录日志
    public function onBufferFull(...$params) : void {} //TODO 记录日志
    public function onClose(...$params) : void {}
    public function onConnect(...$params) : void {}
    public function onError(...$params) : void {} //TODO 记录日志

    public function onMessage(...$params) : void {
        self::_analysis(...$params);
        if(self::$_exception instanceof RpcException){
            $this->_error(self::$_exception);
            return;
        }
        if(self::$_exception instanceof \Throwable){
            $this->_error(new ServerErrorException(), self::$_exception);
            return;
        }
        try {
            if(!self::$_jsonFormat->result = Router::dispatch(
                self::$_jsonFormat->id ? 'normal' : 'notice',
                self::$_jsonFormat->method
            )){
                dump(self::$_jsonFormat->result);
                $this->_error(new ServerErrorException(), '500 SERVER ERROR');
                return;
            }
            $this->_success(self::$_jsonFormat->id ?
                self::$_jsonFormat->outputArrayByKey(true, self::$_jsonFormat::TYPE_RESPONSE) :
                null
            );
            return;
        }catch (\Throwable $exception){
            if($exception->getCode() === 404){
                $this->_error(new MethodNotFoundException(), '404 NOT FOUND');
                return;
            }
            $this->_error(new ServerErrorException(), $exception->getPrevious() ?? $exception);
            return;
        }

    }

    protected static function _analysis(...$params) : void {
        [self::$_connection, $data] = $params;
        [self::$_exception, $buffer] = (array)$data;
        self::$_jsonFormat = JsonFmt::factory((array)$buffer);
        self::$_params = self::$_jsonFormat->params;
    }

    protected function _error(RpcException $exception, $info = null) : void {
        $errorFmt = ErrorFmt::factory();
        $errorFmt->code    = $exception->getCode();
        $errorFmt->message = $exception->getMessage();
        if($info instanceof \Throwable){
            $info = self::$_debug ? [
                '_code'    => $exception->getCode(),
                '_message' => $exception->getMessage(),
                '_file'    => $exception->getFile() . '(' .$exception->getLine(). ')',
                '_trace'   => $exception->getTraceAsString(),
                '_info'    => $exception instanceof ServiceErrorException ? $exception->getInfo() : []
            ] : [
                '_code'    => $exception->getCode(),
                '_message' => $exception->getMessage(),
            ];
        }
        $errorFmt->data = $info ?? null;
        self::$_jsonFormat->error = $errorFmt->outputArray($errorFmt::FILTER_STRICT);
        if(!self::connection()->send(self::$_jsonFormat->outputArrayByKey(true, self::$_jsonFormat::TYPE_RESPONSE))){
            self::log('Failed to send error message  ' . self::$_jsonFormat->id);
        }
    }
    
    protected function _success(?array $buffer) : void{
        if(!self::connection()->send($buffer)){
            self::log('Failed to send success message  ' . self::$_jsonFormat->id);
        }
    }
}
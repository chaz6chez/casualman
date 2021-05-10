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
use Protocols\JsonRpc2;

class RpcServer extends AbstractProcess implements ListenerInterface{

    protected static $_connection;
    protected static $_params;
    protected static $_debug = false;

    public function onStart(...$param): void
    {
        dump('on start');
    }
    public function onReload(...$param): void
    {
        dump('on reload');
    }
    public function onStop(...$param): void
    {
        dump('on stop');
    }
    public function onBufferDrain(...$params) : void
    {
        dump('on buffer drain');
    }
    public function onBufferFull(...$params) : void
    {
        dump('on buffer full');
    }
    public function onClose(...$params) : void
    {
        dump('on close');
    }
    public function onConnect(...$params) : void
    {

        dump('on connect');
    }
    public function onError(...$params) : void
    {
        dump('on error');
    }

    public function onMessage(...$params) : void
    {
        [$connection ,$data] = $params;
        list($exception, $buffer) = $data;
        $fmt = JsonFmt::factory((array)$buffer);
        self::$_params = $fmt->params;


        # 异常检查
        if($exception instanceof RpcException){
            $this->_throwError($exception);
            return;
        }
        if($exception instanceof \Exception){
            $this->_throwError(new ServerErrorException(), $exception);
            return;
        }
        try {
            $resFmt = JsonFmt::factory();
            $resFmt->id = $fmt->id ?? null;
            if(!$resFmt->result = Router::dispatch($fmt->id ? 'normal' : 'notice', $fmt->method)){
                $this->_throwError(new ServerErrorException(), '500 SERVER ERROR');
                return;
            }
            $connection->send(JsonRpc2::encode($resFmt->id ? (array)$buffer : null, $connection));
            return;
        }catch (\Throwable $exception){
            if($exception->getCode() === 404){
                $this->_throwError(new MethodNotFoundException(), '404 NOT FOUND');
                return;
            }
            $this->_throwError(new ServerErrorException(), $exception->getPrevious() ?? $exception);
            return;
        }

    }
    public static function connection(){
        return self::$_connection;
    }

    public static function params(){
        return self::$_params;
    }

    public static function debug(bool $debug = false){
        self::$_debug = $debug;
    }

    protected function _throwError(\Throwable $exception, $info = null) : void{
        $resFmt = JsonFmt::factory();
        $errorFmt = ErrorFmt::factory();
        $errorFmt->code    = $exception->getCode();
        $errorFmt->message = $exception->getMessage();
        if($info){
            $errorFmt->data = $info instanceof \Exception ? $this->_debugInfo($info) : $info;
        }
        $resFmt->error   = $errorFmt->outputArray($errorFmt::FILTER_STRICT);
        self::connection()->send(
            JsonRpc2::encode($resFmt->outputArrayByKey($resFmt::FILTER_STRICT, $resFmt::TYPE_RESPONSE),
                self::connection())
        );
    }

    /**
     * @param \Throwable $exception
     * @return array
     */
    protected function _debugInfo(\Throwable $exception) : array{
        if(self::$_debug){
            return [
                '_code'    => $exception->getCode(),
                '_message' => $exception->getMessage(),
                '_file'    => $exception->getFile() . '(' .$exception->getLine(). ')',
                '_trace'   => $exception->getTraceAsString(),
                '_info'    => $exception instanceof ServiceErrorException ? $exception->getInfo() : []
            ];
        }

        return [
            '_code'    => $exception->getCode(),
            '_message' => $exception->getMessage(),
        ];
    }
}
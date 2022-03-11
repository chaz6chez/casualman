<?php
declare(strict_types=1);

namespace CasualMan\Process;

use Kernel\ApplicationFactory;
use Kernel\Routers\HttpRouter;
use Psr\Log\LoggerInterface;
use Kernel\AbstractProcess;
use Kernel\Protocols\ListenerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;

class HttpServer extends AbstractProcess implements ListenerInterface
{
    /** @var TcpConnection|null */
    protected static $_connection;

    /** @var Http\Request|null */
    protected static $_request;

    /** @var int|null */
    protected static $_start;

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

    public static function request() : ?Http\Request
    {
        return self::$_request;
    }

    public function onStart(...$param): void
    {
        if(!$this->_logger instanceof LoggerInterface)
        {
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

        try {
            if(!$result = HttpRouter::dispatch(
                self::request()->method(),
                self::request()->path()
            ) or (!$result instanceof Http\Response)){
                throw new \RuntimeException('Server Error[internal]', 500);
            }
            return;
        }catch (\Exception $exception){
            switch ($exception->getCode()){
                case 403:
                case 404:
                    $result = new Http\Response();
                    $result->withStatus($exception->getCode());
                    $result->withBody(
                        json_encode(["{$exception->getCode()} {$exception->getMessage()}"],JSON_UNESCAPED_UNICODE)
                    );
                    break;
                default:
                    $result = new Http\Response();
                    $result->withStatus(500);
                    $result->withBody(
                        json_encode(["500 {$exception->getMessage()}"],JSON_UNESCAPED_UNICODE)
                    );
                    break;
            }
            return;
        } finally {
            if(!$result->getHeader('Content-Type')){
                $result->header('Content-Type', 'application/json');
            }
            $result->header('Connection', self::request()->header('Connection'));
            $result->header('Server', ApplicationFactory::$name);
            $result->header('Version', ApplicationFactory::$version);
            $this->_response($result);
            return;
        }
    }

    protected function _init(...$params) : void
    {
        self::$_start  = microtime(true);
        list(self::$_connection, self::$_request) = $params;

        if(is_callable($this->_before)){
            ($this->_before)($this);
        }
    }

    protected function _response(Http\Response $buffer) : void
    {
        if(DEBUG){
            $buffer->header('Duration', microtime(true) - self::$_start);
        }
        (self::request()->header('Connection') === 'keep-alive')
            ? self::connection()->send($buffer)
            : self::connection()->close($buffer);

        if(is_callable($this->_after)){
            ($this->_after)($this);
        }
    }

    protected function _clean() : void {
        self::$_connection = null;
        self::$_request = null;
    }
}
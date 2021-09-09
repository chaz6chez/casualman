<?php
declare(strict_types=1);

namespace CasualMan\Common\Process;

use Kernel\Routers\HttpRouter;
use Kernel\AbstractProcess;
use Kernel\Protocols\ListenerInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use RuntimeException;
use Workerman\Protocols\Http\Response;

class HttpServer extends AbstractProcess implements ListenerInterface
{
    /**
     * @var TcpConnection
     */
    protected static $_connection;

    /**
     * @var Request
     */
    protected static $_request;

    /**
     * @var Response
     */
    protected static $_response;

    /**
     * @return TcpConnection|null
     */
    public static function connection() : ?TcpConnection
    {
        return self::$_connection;
    }

    /**
     * @param TcpConnection $connection
     */
    public static function setConnection(TcpConnection $connection) : void
    {
        self::$_connection = $connection;
    }

    /**
     * @return Request|null
     */
    public static function request(): ?Request
    {
        return self::$_request;
    }

    /**
     * @param Request $request
     */
    public static function setRequest(Request $request): void
    {
        self::$_request = $request;
    }

    /**
     * @return Response
     */
    public static function response(): Response
    {
        self::$_response = new Response();
        self::$_response->header('Server','Casual-Man');
        self::$_response->withStatus(200);
        return self::$_response;
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
        self::setConnection($params[0]);
        self::setRequest($params[1]);
        try {
            if(!$result = HttpRouter::dispatch(
                self::request()->method(),
                self::request()->path()
            )){
                throw new RuntimeException('SERVER ERROR', 500);
            }
            if(self::request()->header('connection') !== 'keep-alive'){
                self::connection()->close($result);
            }else{
                self::connection()->send($result);
            }
            return;
        }catch (\Throwable $exception){
            $response = self::response();
            $response->header('Content-Type','application/json');
            $response->withStatus($exception->getCode());
            $response->withBody(json_encode([
                'status' => -1,
                'code'   => $exception->getCode(),
                'message'=> $exception->getMessage()
            ],JSON_UNESCAPED_UNICODE));
            self::connection()->close($response);
            return;
        }
    }
}
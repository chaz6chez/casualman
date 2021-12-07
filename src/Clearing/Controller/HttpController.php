<?php
declare(strict_types=1);
namespace CasualMan\Clearing\Controller;

use CasualMan\Clearing\Error\ErrorCode;
use CasualMan\Common\Internal\AbstractController;
use CasualMan\Common\Process\HttpServer;
use Utils\Context;
use Workerman\Protocols\Http\Response;

class HttpController extends AbstractController {

    public function init()
    {
        $this->setConnection(HttpServer::connection());
    }

    public function reject() : Response
    {
        return $this->error(ErrorCode::ILLEGAL_REQUEST, null, HttpServer::request()->method());
    }

    public function response(array $data)  : Response
    {
        if(DEBUG){
            $data['_sql'] = Context::context();
        }
        $response = new Response();
        $response->withStatus(200);
        $response->withBody(json_encode($data,JSON_UNESCAPED_UNICODE));
        return $response;
    }

    public function success($data, $msg = 'success'): Response
    {
        return $this->response([
            'data'   => $data,
            'msg'    => $msg,
            'code'   => '0',
            'status' => true
        ]);
    }

    public function error($msg, $code = '500', $data = []) : Response
    {
        if($res = self::analyzeError($msg)){
            list($code, $msg) = $res;
        }
        return $this->response([
            'data'   => $data,
            'msg'    => $msg,
            'code'   => $code,
            'status' => false
        ]);
    }
}
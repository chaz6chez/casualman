<?php
declare(strict_types=1);
namespace CasualMan\Application\Controller;

use CasualMan\Application\Error\ErrorCode;
use CasualMan\Common\AbstractController;
use CasualMan\Process\RpcServer;
use Utils\JsonRpc2\Exception\ServiceErrorException;
use Utils\JsonRpc2\Format\JsonFmt;

class RpcController extends AbstractController {

    public function init()
    {
        $this->setConnection(RpcServer::connection());
    }

    public function getJsonFormat() : ?JsonFmt
    {
        return RpcServer::jsonFormat();
    }

    public function getRawData() : string
    {
        return RpcServer::getRaw();
    }

    public function reject() : array
    {
        return $this->error(ErrorCode::REQUEST_REJECTED, null);
    }

    public function success($data, $msg = 'success')  : array
    {
        return [
            'data'   => $data,
            'msg'    => $msg,
            'status' => true,
            'version'=> S_VERSION
        ];
    }

    public function error($msg, $code = 0, $data = []) : array
    {
        if($res = self::analyzeError($msg)){
            list($code, $msg) = $res;
        }
        return [
            'data'   => $data,
            'msg'    => $msg,
            'code'   => $code,
            'status' => false,
            'version'=> S_VERSION
        ];
    }

    public static function isServerError($data) : bool
    {
        if(is_array($data)){
            if(isset($data['status']) and $data['status']){
                return false;
            }
            if(isset($data['code'])){
                return ErrorCode::exception($data['code']);
            }
        }
        return true;
    }

    public static function throwServiceError(array $data){
        $msg = isset($data['msg']) ? $data['msg'] : 'Service error';
        $code  = isset($data['code']) ? $data['code'] : null;
        $data = isset($data['data']) ? $data['data'] : [];
        throw new ServiceErrorException($msg, $code, $data);
    }
}
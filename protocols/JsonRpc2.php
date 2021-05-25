<?php

namespace Protocols;

use CasualMan\Common\Internal\JsonRpc2\Exception\InvalidRequestException;
use CasualMan\Common\Internal\JsonRpc2\Exception\ParseErrorException;
use CasualMan\Common\Internal\JsonRpc2\Exception\RpcException;
use CasualMan\Common\Internal\JsonRpc2\Format\JsonFmt;

/**
 * JsonRpc-2.0 协议
 *
 * Class JsonRpc2
 * @package Protocols
 * @link https://www.jsonrpc.org/
 * @license https://www.jsonrpc.org/specification
 */
class JsonRpc2 {

    public static $buffer;
    /**
     * 检查包的完整性
     * @param $buffer
     * @param mixed ...$params
     * @return int
     */
    public static function input($buffer, ...$params) : int{
        # 获得换行字符"\n"位置
        $pos = strpos($buffer, "\n");
        // 没有换行符
        if($pos === false) {
            // 无法得知包长，返回0继续等待数据
            return 0;
        }
        // 有换行符，返回当前包长（包含换行符）
        return $pos + 1;
    }

    /**
     * 打包
     * @param $buffer
     * @param mixed ...$params
     * @return string
     * @throws RpcException
     */
    public static function encode($buffer, ...$params) : string {
        if(!is_array($buffer)){
            # 抛出ParseError异常
            throw new ParseErrorException();
        }
        if(!$buffer){
            throw new InvalidRequestException();
        }
        return json_encode($buffer) . "\n";
    }

    /**
     * 解包
     * @param $buffer
     * @param mixed ...$params
     * @return array
     * @throws RpcException
     */
    public static function decode($buffer, ...$params) : array {
        self::$buffer = $buffer;
        $data = self::isJson(trim($buffer),true);
        # 不是json
        if($data === false){
            # 抛出ParseError异常
            throw new ParseErrorException();
        }
        # 空数组
        if(!$data){
            throw new InvalidRequestException();
        }
        return $data;
    }

    /**
     * 响应检验
     *
     *  应在解包之后调用
     * @param array $data
     * @return array
     */
    public static function response(array $data) : array {
        $fmt = JsonFmt::factory();
        if(!self::isAssoc($data)){
            foreach($data as $value){
                if(($res = self::_throw($fmt, $value, $fmt::TYPE_RESPONSE)) !== true){
                    return self::_res($res, null);
                }
            }
        }
        if(($res = self::_throw($fmt, $data, $fmt::TYPE_RESPONSE)) !== true){
            return self::_res($res, null);
        }
    }

    /**
     * 请求检验
     *
     *  应在打包之前调用
     * @param array $data
     * @return array
     */
    public static function request(array $data) : array {
        $fmt = JsonFmt::factory();
        # 不是关联数组
        if(!self::isAssoc($data)){
            foreach($data as $value){
                if(($res = self::_throw($fmt, $value, $fmt::TYPE_REQUEST)) !== true){
                    return self::_res($res, null);
                }
            }
        }
        if(($res = self::_throw($fmt, $data, $fmt::TYPE_REQUEST)) !== true){
            return self::_res($res, null);
        }
        return self::_res(true, $data);
    }

    /**
     * @param JsonFmt $fmt
     * @param $data
     * @param $scene
     * @return RpcException|bool
     */
    protected static function _throw(JsonFmt $fmt, $data, $scene){
        $fmt->clean(true);
        $fmt->setScene($scene);
        $fmt->create($data,true);
        # 如果有错误
        if($fmt->hasError()){
            # 抛出异常
            $exception = $fmt->getError();
            $exception = "CasualMan\Common\Internal\JsonRpc2\Exception\\{$exception}";
            return new $exception;
        }
        # 如果有特殊错误
        if($fmt->hasSpecialError()){
            # 抛出异常
            $exception = $fmt->getSpecialError();
            $exception = "CasualMan\Common\Internal\JsonRpc2\Exception\\{$exception}";
            return new $exception;
        }
        return true;
    }

    /**
     * @param $exception
     * @param $data
     * @return array
     */
    protected static function _res($exception, $data) : array {
        return [
            $exception,
            $data
        ];
    }

    /**
     * 是否是Json
     * @param $string
     * @param bool $get
     * @return bool|mixed
     */
    public static function isJson($string, bool $get = false){
        @json_decode($string);
        if(json_last_error() != JSON_ERROR_NONE){
            return false;
        }
        if($get){
            return json_decode($string,true);
        }
        return true;
    }

    /**
     * 是否是索引数组
     * @param array $array
     * @return bool
     */
    public static function isAssoc(array $array){
        return boolval(array_keys($array) !== range(0, count($array) - 1));
    }

}


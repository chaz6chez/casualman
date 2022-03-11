<?php
declare(strict_types=1);

namespace CasualMan\Common;

use Utils\JsonRpc2\Exception\ConnectException;
use Utils\JsonRpc2\Exception\InternalErrorException;
use Utils\JsonRpc2\Exception\InvalidRequestException;
use Utils\JsonRpc2\Exception\MethodAlreadyException;
use Utils\JsonRpc2\Exception\MethodNotFoundException;
use Utils\JsonRpc2\Exception\MethodNotReadyException;
use Utils\JsonRpc2\Exception\RpcException;
use Utils\JsonRpc2\Exception\ServerErrorException;
use Utils\JsonRpc2\Format\ErrorFmt;
use Utils\JsonRpc2\Format\JsonFmt;
use Protocols\JsonRpc2;

class RpcClient
{
    /**
     * @var array 服务端地址
     */
    protected static array $_address = [];

    /**
     * @var RpcClient 同步调用实例
     */
    protected static $_instances = null;
    /**
     * @var resource|null 服务端的socket连接
     */
    protected $_socket = null;

    /**
     * @var int 连接超时时间
     */
    protected $_connectTimeout;
    /**
     * @var int 请求超时时间
     */
    protected $_requestTimeout;

    protected $_prepares   = false;

    /**
     * RpcClient constructor.
     * @param array $address
     * @param int $timeout
     */
    protected function __construct(array $address, int $timeout) {
        self::$_address = $address;
        $this->_connectTimeout = $timeout;
    }

    /**
     * @return null|int
     */
    public function getConnectTimeout(): ?int
    {
        return $this->_connectTimeout;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->_requestTimeout;
    }

    /**
     * @param int $requestTimeout
     * @return $this
     */
    public function setTimeout(int $requestTimeout = 90) : RpcClient
    {
        $this->_requestTimeout = $requestTimeout;
        stream_set_timeout($this->_socket, $this->getTimeout());
        return $this;
    }


    /**
     * @param array $address
     * @param int $timeout
     * @return RpcClient
     */
    public static function instance(array $address = [], int $timeout = 5) :RpcClient
    {

        if(!static::$_instances or !static::$_instances instanceof RpcClient) {
            static::$_instances = new static($address, $timeout);
        }
        return self::$_instances;
    }

    /**
     * @return resource|null
     * @throws ConnectException
     */
    protected function _connection()
    {
        if(
            !$this->_socket or
            !is_resource($this->_socket)
        ){
            $res = @stream_socket_client(
                self::$_address[array_rand(self::$_address)],
                $err_no,
                $err_msg,
                $this->getConnectTimeout()
            );
            if(!$res or $err_no){
                throw new ConnectException();
            }
            $this->_socket = $res;
        }
        stream_set_blocking($this->_socket, true);
        $this->setTimeout();
        return $this->_socket;
    }

    /**
     * 关闭连接
     */
    public function close(){
        if(is_resource($this->_socket)){
            @fclose($this->_socket);
        }
        $this->_socket = null;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @param string|null $id
     * @return array [tag, data]
     *               true:  成功
     *               false: 失败
     */
    public function call(string $method, array $arguments, ?string $id = null) :array
    {
        $res = $this->send($method, $arguments, $id);
        if($res){
            return $this->_res(false,$res);
        }
        if($id){
            $res = $this->get();
            if(
                is_array($res) and
                isset($res['error']['data']) and
                $res['error']['data']=== '<get>'
            ){
                return $this->_res(false,$res);
            }
        }
        return $this->_res(true,$res);
    }

    /**
     * @param $tag
     * @param array|null $data
     * @return array
     */
    protected function _res($tag, ?array $data) :array
    {
        return [
            $tag,
            $data
        ];
    }

    /**
     * 发送
     * @param string $data
     * @return false|int
     * @throws ConnectException
     */
    public function sendRaw(string $data) {
        return fwrite($this->_connection(), $data, strlen($data));
    }

    /**
     * 获取
     * @return false|string
     * @throws ConnectException
     */
    public function getRaw() {
        return fgets($this->_connection());
    }

    /**
     * @param string $method
     * @param array $arguments
     * @param string $id
     * @return array|null
     *  null success
     *  array 协议错误/连接错误/发送失败
     */
    public function send(string $method, array $arguments, $id = '') :?array
    {
        $fmt         = JsonFmt::factory();
        $fmt->method = $method;
        $fmt->params = $arguments;
        $fmt->id     = $id ? $id : null;
        try {
            $res = $this->sendRaw(
                JsonRpc2::encode($fmt->filter(STRUCT_FILTER_EMPTY,STRUCT_FILTER_NULL)->output())
            );
            if($res === false){
                throw new InvalidRequestException();
            }
            return null;
        }catch(RpcException $rpcException){
            $error       = ErrorFmt::factory();
            $error->code    = $rpcException->getCode();
            $error->message = $rpcException->getMessage();
            $error->data    = '<send>';
            $fmt->error     = $error->output();
            return $fmt->scene($fmt::TYPE_RESPONSE)->filter(STRUCT_FILTER_KEY_REVERSE)->output();
        }
    }

    /**
     * 从服务端接收数据
     * @return array
     */
    public function get() :array
    {
        $fmt = JsonFmt::factory();
        try {
            if(($buffer = $this->getRaw()) === false){
                throw new InvalidRequestException();
            }
            return ($buffer !== PHP_EOL) ? JsonRpc2::decode($buffer, $this->_prepares) : [];
        }catch(RpcException $rpcException){
            $error       = ErrorFmt::factory();
            $error->code    = $rpcException->getCode();
            $error->message = $rpcException->getMessage();
            $error->data    = '<get>';
            $fmt->error     = $error->output();
            return $fmt->scene($fmt::TYPE_RESPONSE)->filter(STRUCT_FILTER_KEY_REVERSE)->output();
        }
    }

    /**
     * @param $prefix
     * @return string
     */
    public static function uuid($prefix = '') : string {
        if(extension_loaded('uuid') and function_exists('uuid_create')){
            return $prefix . uuid_create(1);
        }
        $chars = md5(uniqid(mt_rand(), true));
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;

    }
}

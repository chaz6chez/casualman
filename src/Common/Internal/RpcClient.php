<?php

namespace CasualMan\Common\Internal;

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

class RpcClient {

    /**
     * @var int 发送数据和接收数据的超时时间  单位S
     */
    const TIME_OUT = 5;

    /**
     * @var array 服务端地址
     */
    protected static $_addressArray = [];

    /**
     * @var string 异步调用实例
     */
    protected static $_asyncInstances = [];
    /**
     * @var RpcClient 同步调用实例
     */
    protected static $_instances = null;
    /**
     * @var resource 服务端的socket连接
     */
    protected $_connection = null;
    protected $_id         = null; # 实例id
    protected $_async_id   = null; # 上一个激活的异步客户端id
    protected $_prepares   = false;
    protected $_buffer     = null;
    protected $_timeout    = self::TIME_OUT;

    /**
     * RpcClient constructor.
     * @param array $address
     */
    protected function __construct(array $address) {
        if($address){
            self::$_addressArray = $address;
        }
    }

    /**
     * @param array $address
     * @return static
     */
    public static function factory(array $address = []){
        return new static($address);
    }

    /**
     * @param array $address
     * @return RpcClient
     */
    public static function instance(array $address = []) {

        if(!static::$_instances or !static::$_instances instanceof RpcClient) {
            static::$_instances = new static($address);
        }
        return self::$_instances;
    }

    public static function getInstance(){
        return self::$_instances;
    }

    public static function getAsyncInstance(){
        return self::$_asyncInstances;
    }

    /**
     * 设置是否本地预处理服务器返回值
     * @param bool $prepares
     * @return $this
     */
    public function prepares(bool $prepares){
        $this->_prepares = $prepares;
        return $this;
    }

    /**
     * 获取缓冲区数据
     * @return string
     */
    public function getBuffer(){
        return $this->_buffer;
    }

    /**
     * 设置缓冲区数据
     * @param $buffer
     */
    public function setBuffer($buffer){
        $this->_buffer = $buffer;
    }

    /**
     * 获取超时时间
     * @return string
     */
    public function getTimeout(){
        return $this->_timeout;
    }

    /**
     * 设置超时时间
     * @param int $time
     */
    public function setTimeout(int $time){
        $this->_timeout = $time;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @param string $id
     * @return array
     *
     * return[0] = key            asyncRecv的必要入参，key = method:id
     *
     * return[1] = JsonEmt.object 表示有异常
     *             false          表示连接失败
     *             true           表示成功
     *
     * @throws MethodAlreadyException
     */
    public function asyncSend(string $method, array $arguments, $id) {
        $key = "{$method}:{$id}";
        if(
            isset(static::$_asyncInstances[$key]) and
            static::$_asyncInstances[$key] instanceof RpcClient
        ) {
            throw new MethodAlreadyException($key);
        }
        $async = static::$_asyncInstances[$key] = static::factory(static::$_addressArray);
        $this->_async_id                        = $key;

        return $this->_res(
            $async->_sendData($method, $arguments, $id),
            $key
        );
    }

    /**
     * @param string $key
     * @return array
     *
     * return[0] = tag            bool|null
     *                            true:  成功
     *                            false: 内部错误|连接错误
     *                            null:  jsonRpc-2.0协议错误
     *
     * return[1] = data           array 数据
     *
     * @throws MethodNotReadyException
     */
    public function asyncRecv($key) {
        if(
            isset(self::$_asyncInstances[$key]) and
            (($async = self::$_asyncInstances[$key]) instanceof RpcClient)
        ){
            $res = $async->_recvData();
            $async->close();
            self::$_asyncInstances[$key] = null;
            $this->_async_id = ($this->_async_id === $key) ? null : $this->_async_id;
            if($res === false){
                return $this->_res(['connection error -> async_recv'],false);
            }
            if($res instanceof JsonFmt){
                return $this->_res($res->outputArray(),null);
            }
            return $this->_res($res, true);
        }
        throw new MethodNotReadyException($key);
    }

    /**
     * 异步通知发送
     * @param string $method
     * @param array $arguments
     * @return array
     *
     * return[0] = JsonEmt.object 表示有异常
     *             false          表示连接失败
     *             true           表示成功
     *
     * return[1] = data
     */
    public function asyncNoticeSend(string $method, array $arguments) {
        $async = self::instance(self::$_addressArray);
        return $this->_res(
            null,
            $async->_sendData( $method, $arguments)
        );
    }

    /**
     * 同步
     * @param string $method
     * @param array $arguments
     * @param string $id
     * @return array
     *
     * return[0] = tag            bool|null
     *                            true:  成功
     *                            false: 内部错误|连接错误
     *                            null:  jsonRpc-2.0协议错误
     *
     * return[1] = data           array 数据
     *
     */
    public function call(string $method, array $arguments, $id = '') {
        $res = $this->_sendData($method, $arguments, $id);
        if($res === false){
            return $this->_res(['connection error -> send'],false);
        }
        if($res instanceof JsonFmt){
            return $this->_res($res->outputArray(),null);
        }

        if($id === ''){
            $this->close();
            return $this->_res($res, true);
        }
        $res = $this->_recvData();
        $this->close();
        if($res === false){
            return $this->_res(['connection error -> recv'],false);
        }
        if($res instanceof JsonFmt){
            return $this->_res($res->outputArray(),null);
        }
        return $this->_res($res, true);
    }

    /**
     * 发送
     * @param string $json
     * @param int $timeout
     * @return bool
     */
    public function send(string $json, $timeout = 5) {
        try {
            $this->setTimeout($timeout);
            return boolval(fwrite($this->_openConnection( true), $json) !== strlen($json));
        }catch(ConnectException $connectException){
            return false;
        }catch(\Exception $exception){
            return false;
        }
    }

    /**
     * 获取
     * @param bool $close
     * @return array
     */
    public function get($close = true){
        $res = [];
        while($this->_buffer = fgets($this->_connection)){
            $res[] = $this->_buffer;
        }
        if($close){
            $this->close();
        }
        return $res;
    }

    /**
     * 关闭连接
     */
    public function close(){
        $this->_closeConnection();
    }

    /**
     * @param mixed $data 数据
     * @param bool|string $tag 标记
     * @return array
     */
    protected function _res($data, $tag){
        return [
            $tag,
            $data
        ];
    }

    /**
     * 发送数据给服务端
     * @param $method
     * @param $arguments
     * @param $id
     * @return bool|JsonFmt
     *
     * JsonEmt.object 表示有异常
     * false          表示连接失败
     * true           表示成功
     */
    protected function _sendData(string $method, array $arguments, $id = '') {
        $fmt         = JsonFmt::factory();
        $fmt->method = $method;
        $fmt->params = $arguments;
        $fmt->id     = $id ? $id : null;
        $error       = ErrorFmt::factory();
        try {
            $json = JsonRpc2::encode($fmt->outputArray($fmt::FILTER_STRICT));
            # 发送数据
            if(($a = fwrite($this->_openConnection(), $json)) !== strlen($json)) {
                throw new InvalidRequestException();
            }
            return $a;
        }catch(ConnectException $connectException){
            return false;
        }catch(RpcException $rpcException){
            $error->code    = $rpcException->getCode();
            $error->message = $rpcException->getMessage();
            $fmt->error     = $error->outputArray();
            return $fmt;
        }catch(\Exception $exception){
            $serverException = new ServerErrorException();
            $error->code    = $serverException->getCode();
            $error->message = $serverException->getMessage();
            $error->data    = [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode()
            ];
            $fmt->error     = $error->outputArray();
            return $fmt;
        }
    }

    /**
     * 从服务端接收数据
     * @return array|bool|JsonFmt
     *
     * JsonEmt.object 表示有异常
     * false          表示连接失败
     * array          表示成功
     */
    protected function _recvData() {
        $fmt         = JsonFmt::factory();
        $error       = ErrorFmt::factory();
        try {
            if(!is_resource($this->_connection)){
                return false;
            }
            $this->setBuffer(null);
            $this->setBuffer(fgets($this->_connection));

            if($this->getBuffer() !== "\n"){
                return JsonRpc2::decode($this->getBuffer(), $this->_prepares);
            }
            return [];
        }catch(ConnectException $connectException){
            return false;
        }catch(RpcException $rpcException){
            $error->code    = $rpcException->getCode();
            $error->message = $rpcException->getMessage();
            $fmt->error     = $error->outputArray();
            return $fmt;
        }catch(\Exception $exception){
            $serverException = new ServerErrorException();
            $error->code    = $serverException->getCode();
            $error->message = $serverException->getMessage();
            $error->data    = [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode()
            ];
            $fmt->error     = $error->outputArray();
            return $fmt;
        }
    }

    /**
     * 打开连接
     * @param bool $mode
     * @return resource
     * @throws ConnectException
     */
    protected function _openConnection($mode = true) {
        if(!is_resource($this->_connection)){
            $this->_connection = @stream_socket_client(
                self::$_addressArray[array_rand(self::$_addressArray)],
                $err_no,
                $err_msg
            );
        }
        if(!$this->_connection or !is_resource($this->_connection)) {
            throw new ConnectException();
        }
        stream_set_blocking($this->_connection, $mode);
        if($this->getTimeout()){
            stream_set_timeout($this->_connection, $this->getTimeout());
        }
        return $this->_connection;
    }

    /**
     * 关闭连接
     */
    protected function _closeConnection() {
        if(is_resource($this->_connection)){
            fclose($this->_connection);
        }
        $this->_connection = null;
        $this->_id         = null;
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

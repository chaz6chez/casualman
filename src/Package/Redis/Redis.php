<?php
declare(strict_types=1);

namespace CasualMan\Package\Redis;

use Utils\Tools;

class Redis extends Driver
{

    protected $options   = [];
    protected $is_active = null;
    /**
     * @var Redis
     */
    private static $_instance = null;

    /**
     * Redis constructor.
     * @param array $options 缓存参数
     */
    public function __construct($options = []) {
        $this->options = $options ?: C('package.redis.config.redis.default');
        if (!$this->handler or !$this->handler instanceof \Redis) {
            $this->handler = new \Redis();
        }
        try{
            if ($this->options['persistent']) {
                $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
            } else {
                $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
            }

            if ('' != $this->options['password']) {
                $this->handler->auth($this->options['password']);
            }

            if (0 != $this->options['select']) {
                $this->handler->select($this->options['select']);
            }
        }catch (\Exception $e){
            $this->is_active = false;
            //todo 日志
        }
    }

    /**
     * 实例
     * @return Redis
     */
    final public static function instance() : Redis
    {
        return Co()->get(get_called_class());
    }

    /**
     * 检查后删除
     * @param string $name
     * @return bool
     */
    public function rmAfterCheck(string $name) : bool
    {
        if($this->has($name)){
            return $this->rm($name);
        }
        return true;
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has(string $name) : bool
    {
        if(!$this->call()) return false;
        return $this->handler->exists($this->getCacheKey($name));
    }

    /**
     * @param $name
     * @return array|bool
     */
    public function keys($name){
        if(!$this->call()) return false;
        return $this->handler->keys($this->getCacheKey($name));
    }

    /**
     * @param string $name
     * @param bool $default
     * @return array
     */
    public function getKeysValue(string $name, bool $default = false) : array
    {
        $keys = $this->keys($name);
        $result = [];
        if($keys){
            if(count($keys) < 5000){
                foreach ($keys as $key){
                    $result[] = $this->get($key,$default);
                }
            }
        }
        return $result;
    }

    /**
     * @param string $name
     * @return array|bool
     */
    public function hGetAll(string $name){
        if(!$this->call()) return false;
        $h = $this->handler->hGetAll($this->getCacheKey($name));
        if($h){
            foreach ($h as &$value){
                $value = ($json = $this->_isJson($value,true)) ? $json : $value;
            }
        }
        return $h;
    }

    /**
     * @param $key
     * @return array|bool
     */
    public function hGetAllNew($key) {
        if(!$this->call()) return false;
        $key = $this->getCacheKey($key);
        $keys = $this->handler->hKeys($key);
        $data = [];
        if(!$keys) return $data;
        $json = $this->handler->hMGet($key, $keys);
        $data = ($json = $this->_isJson((string)$json,true)) ? $json : [];
        return $data;
    }


    /**
     * @param $name
     * @param $key
     * @return string|bool
     */
    public function hGet($name,$key){
        if(!$this->call()) return false;
        $value = $this->handler->hGet($this->getCacheKey($name),$key);
        $value = ($json = $this->_isJson($value,true)) ? $json : $value;
        return $value;
    }

    /**
     * @param $name
     * @param array $array
     * @return bool
     */
    public function hSetArray(string $name, array $array) : bool
    {
        if(!$this->call()) return false;
        foreach ($array as $key => $value){
            $v = is_scalar($value) ? $value : json_encode($value,JSON_UNESCAPED_UNICODE);
            $this->handler->hSet($this->getCacheKey($name),$key,$v);
        }
        return true;
    }

    /**
     * @param $name
     * @param $key
     * @param $value
     * @return bool|int
     */
    public function hSet($name,$key,$value){
        if(!$this->call()) return false;
        $v = is_scalar($value) ? $value : json_encode($value,JSON_UNESCAPED_UNICODE);
        return $this->handler->hSet($this->getCacheKey($name),$key,$v);
    }

    /**
     * @param $name
     * @param array|string $keys
     * @return bool|int
     */
    public function hDel($name, $keys){
        if(!$this->call()) return false;
        if(is_array($keys)){
            foreach ($keys as $key){
                $this->handler->hDel($this->getCacheKey($name),$key);
            }
            return true;
        }
        return $this->handler->hDel($this->getCacheKey($name),$keys);
    }


    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $name, $default = false) {
        if(!$this->call()) return false;
        $value = $this->handler->get($this->getCacheKey($name));
        if (is_null($value) || false === $value) {
            return $default;
        }
        try {
            $result = ($json = json_decode($value,true)) ? $json : $value;
        } catch (\Exception $e) {
            $result = $default;
        }
        return $result;
    }

    /**
     * @param $index
     * @return $this
     */
    public function select($index) : self
    {
        if($this->call()) $this->handler->select($index);
        return $this;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param integer|\DateTime $expire 有效时间（秒）
     * @return boolean
     */
    public function set(string $name, $value, $expire = null) : bool
    {
        if(!$this->call()) return false;
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        $value = is_scalar($value) ? $value : json_encode($value,JSON_UNESCAPED_UNICODE);
        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1) {
        if(!$this->call()) return false;
        $key = $this->getCacheKey($name);
        return $this->handler->incrby($key, $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string $name 缓存变量名
     * @param  int $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1) {
        if(!$this->call()) return false;
        $key = $this->getCacheKey($name);
        return $this->handler->decrby($key, $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm(string $name) : bool
    {
        if(!$this->call()) return false;
        return boolval($this->handler->del($this->getCacheKey($name)));
    }

    /**
     * 清除缓存
     * @access public
     * @param string|null $tag 标签名
     * @return boolean
     */
    public function clear(?string $tag = null) : bool
    {
        if(!$this->call()) return false;
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->handler->del($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return $this->handler->flushDB();
    }

    public function call(): bool
    {
        try{
            if(!($this->handler instanceof \Redis)){
                return false;
            }
            $this->handler->ping('');
        }catch (\RedisException $e){
            $this->_log($e);
            if(Tools::isRedisTimeout($e)){
                if($this->options['persistent'] and $this->handler instanceof \Redis){
                    $this->handler->close();
                }
                $this->handler = null;
                $this->is_active = null;
                self::$_instance = null;
                self::$_instance = new self();
                return true;
            }

            return false;
        }catch (\Exception $e){
            $this->_log($e);
            return false;
        }
        return true;
    }

    protected function _log(\Exception $e){
        $logger = C('logger');
        $logger->error('redis exception',[
            'code' => $e->getCode(),
            'msg'  => $e->getMessage()
        ]);
    }

    protected function _isJson(string $string, bool $get = false) {
        @json_decode($string);
        if(json_last_error() != JSON_ERROR_NONE){
            return false;
        }
        if($get){
            return json_decode($string,true);
        }
        return true;
    }
}

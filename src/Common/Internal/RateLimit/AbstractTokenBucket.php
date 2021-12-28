<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal\RateLimit;

use CasualMan\Common\Internal\Redis;
use \Redis as BaseRedis;
use Workerman\Timer;

abstract class AbstractTokenBucket
{
    protected $_driver; //redis实例
    protected $_key;
    protected $_capacity;   //水桶容量
    protected $_qos;    //频次
    protected $_timer;

    /**
     * AbstractTokenBucket constructor.
     * @param string $key
     * @param int $capacity
     * @param int $qos
     */
    final public function __construct(string $key, int $capacity, int $qos)
    {
        $this->_driver = make(Redis::class);
        $this->_key = $key;
        $this->_capacity = $capacity;
        $this->_qos = $qos;
        if($this->driver()->call()){
            if(!$this->redis()->get($this->getTimerName())){
                $this->redis()->set($this->getTimerName(),true);
                if(!$this->_timer){
                    $this->callback();
                    $this->_timer = Timer::add($this->getTimestamp(), [$this, 'callback']);
                }
            }
        }
    }

    /**
     * 析构
     */
    final public function __destruct()
    {
        if($this->_timer){
            Timer::del($this->_timer);
            if($this->driver()->call()){
                $this->redis()->del($this->getTimerName());
            }
        }
    }

    /**
     * @param bool $throw
     * @return bool|null null:频繁 false:限流 true:正常
     */
    final public function __invoke(bool $throw = false): ?bool
    {
        if($this->driver()->call()){
            if (!$this->redis()->lPop($this->_key)) {
                return false;
            }
            return true;
        }
        if($throw){
            throw new \RuntimeException('redis connect failed');
        }
        return true;
    }

    /**
     * @return BaseRedis
     */
    public function redis() : BaseRedis
    {
        return $this->_driver->handler();
    }

    /**
     * @return Redis
     */
    public function driver() : Redis
    {
        return $this->_driver;
    }

    /**
     * @return int|null
     */
    final public function getQuantity() : ?int
    {
        if($this->driver()->call()){
            $res = $this->redis()->lLen($this->_key);
            return $res ?? 0;
        }
        return null;
    }

    /**
     * 获取容量
     * @return int|null
     */
    final public function getCapacity() : ?int
    {
        return $this->_capacity;
    }

    /**
     * 获取Qos
     * @return int|null
     */
    final public function getQos() : ?int
    {
        return $this->_qos;
    }

    /**
     * 定时回调
     */
    public function callback() : void
    {
        if($this->driver()->call()){
            $this->redis()->multi();
            $this->redis()->lPush($this->_key,...array_fill(0, (int)$this->getQos(), '1'));
            $this->redis()->lTrim($this->_key,0, (int)$this->getCapacity() - 1);
            $this->redis()->exec();
        }
    }

    /**
     * 获取定时器名称
     * @return string
     */
    protected function getTimerName() : string
    {
        return 'token_bucket:timer:' . str_replace('\\','.', get_called_class());
    }

    /**
     * 获取定时器时间格式
     * @return int
     */
    abstract public function getTimestamp() :int;
}
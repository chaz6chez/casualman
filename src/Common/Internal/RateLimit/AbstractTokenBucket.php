<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal\RateLimit;

use CasualMan\Common\Internal\Redis;
use \Redis as BaseRedis;
use Workerman\Timer;

abstract class AbstractTokenBucket
{
    /** @var Redis */
    protected $_driver;

    /** @var string 限流key */
    protected $_key;

    /** @var int 容量 */
    protected $_capacity;

    /** @var int 频次 */
    protected $_qos;

    /** @var string[] */
    protected $_timer = [];

    /**
     * AbstractTokenBucket constructor.
     */
    final public function __construct()
    {
        $this->_driver = make(Redis::class);
    }

    /**
     * 析构
     */
    final public function __destruct()
    {
        if($this->_timer){
            foreach ($this->_timer as $key => $timer){
                Timer::del($timer);
                $this->redis()->del($key);
            }
            $this->_timer = [];
        }
    }

    /**
     * @param string $key
     * @param int $capacity
     * @param int $qos
     * @return $this
     */
    final public function __invoke(string $key, int $capacity, int $qos): AbstractTokenBucket
    {
        $this->_key = $key;
        $this->_capacity = $capacity;
        $this->_qos = $qos;
        return $this;
    }

    /**
     * @return bool|null null:频繁 false:限流 true:正常
     */
    public function get(): ?bool
    {
        if($this->driver()->call()){
            if(
                !isset($this->_timer[$this->_key]) and
                !$this->redis()->exists($this->_key)
            ){
                $this->callback();
                $this->_timer[$this->_key] = Timer::add($this->getTimestamp(), [$this, 'callback']);
            }
            if (!$this->redis()->lPop($this->_key)) {
                return false;
            }
            return true;
        }
        return null;
    }

    /**
     * @return BaseRedis
     */
    public function redis() : BaseRedis
    {
        return $this->driver()->handler();
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
    final public function callback() : void
    {
        if($this->driver()->call()){
            $this->redis()->multi();
            $this->redis()->lPush($this->_key,...array_fill(0, (int)$this->getQos(), '1'));
            $this->redis()->lTrim($this->_key,0, (int)$this->getCapacity() - 1);
            $this->redis()->exec();
        }
    }

    /**
     * 获取定时器时间格式
     * @return int
     */
    abstract public function getTimestamp() :int;
}
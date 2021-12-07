<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal\RateLimit;

use CasualMan\Common\Internal\Redis;
use \Redis as BaseRedis;

class SimpleTokenBucket
{
    protected $_driver; //redis实例
    protected $_key;
    protected $_capacity;   //水桶容量
    protected $_qos;    //每秒钟频次

    final public function __construct(string $key, int $capacity, int $qos)
    {
        $this->_driver = make(Redis::class);
        $this->_key = $key;
        $this->_capacity = $capacity;
        $this->_qos = $qos;
    }

    public function redis() : BaseRedis
    {
        return $this->_driver->handler();
    }

    public function driver() : Redis
    {
        return $this->_driver;
    }

    /**
     * @param bool $throw
     * @return bool|null null:频繁 false:限流 true:正常
     */
    final public function __invoke(bool $throw = false): ?bool
    {
        if($this->_driver->call()){
            $now = time();
            $this->redis()->watch($this->_key);
            $res = $this->redis()->get($this->_key);
            $data = $res ? json_decode($res, true) : [
                'capacity' => $this->_qos,
                'last_time' => $now
            ];
            if (($new = (($data['capacity'] - 1) + $this->_number($now - $data['last_time']))) <= 0) {
                return false;
            }
            $this->redis()->multi();
            $this->redis()->set($this->_key, json_encode(['capacity' => $new, 'last_time' => $now]));
            if (!$this->redis()->exec()) {
                return null;
            }
            return true;
        }
        if($throw){
            throw new \RuntimeException('redis connect failed');
        }
        return true;
    }

    final public function getQuantity() : ?int
    {
        if($this->_driver->call()){
            $res = $this->redis()->get($this->_key);
            return isset($res['capacity'])
                ? $res['capacity'] + $this->_number(time() - $res['last_time'])
                : $this->_qos;
        }
        return null;
    }

    final public function getCapacity() : ?int
    {
        return $this->_capacity;
    }

    final public function getQos() : ?int
    {
        return $this->_qos;
    }

    private function _number(int $timestamp): int
    {
        $add = $this->_qos * $timestamp;
        return $add >= $this->_capacity ? $this->_capacity : $add;
    }
}
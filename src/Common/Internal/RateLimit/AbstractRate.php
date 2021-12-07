<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal\RateLimit;

abstract class AbstractRate {
    const BASE_KEY = '#BASE';

    const RED = -1;
    const YELLOW = 0;
    const GREEN = 1;

    /**
     * @var SimpleTokenBucket
     */
    protected $_driver;
    protected $_config; //rate.php 配置
    protected $_qos;    //每秒钟频次
    protected $_capacity;   //水桶容量

    abstract public function key(): string;

    final public function isEnable(): bool
    {
        return !boolval($this->_config === []);
    }

    final public function __construct()
    {
        $this->_config = C('rate.' . $this->key(), C('rate.' . self::BASE_KEY, []));
        if(!$this->isEnable()){
            throw new \RuntimeException("Not Found {$this->key()} Rate Service");
        }
        $this->_qos = isset($this->_config['qos']) ? (int)$this->_config['qos'] : null;
        $this->_capacity = isset($this->_config['capacity']) ? (int)$this->_config['capacity'] : null;
        $this->_driver = make(SimpleTokenBucket::class, $this->key(), $this->_capacity, $this->_qos);
    }

    final public function health() : int
    {
        $percentage = round($this->_driver->getQuantity() / $this->_driver->getCapacity(), 2);
        switch (true) {
            case $percentage < 0.4 and $percentage >= 0.2:
                return self::YELLOW;
            case $percentage < 0.2:
                return self::RED;
            default:
                return self::GREEN;
        }
    }

    final public function limit(): ?bool
    {
        return ($this->_driver)();
    }
}
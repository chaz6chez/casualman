<?php
declare(strict_types=1);

namespace CasualMan\Package\RateLimit;

abstract class AbstractRate {

    const BASE_KEY = '#BASE';

    const RED = -1;
    const YELLOW = 0;
    const GREEN = 1;

    /**
     * @var SimpleTokenBucket
     */
    protected $_driver;

    abstract public function key(): string;

    final public function __construct()
    {
        $config = C('package.rate_limit.config.' . $this->key(), C('package.rate_limit.config.' . self::BASE_KEY, []));
        if(!$config){
            throw new \RuntimeException("Not Found {$this->key()} Rate Service");
        }
        $qos = isset($config['qos']) ? (int)$config['qos'] : null;
        $capacity = isset($config['capacity']) ? (int)$config['capacity'] : null;
        $interval = isset($config['interval']) ? (int)$config['interval'] : null;

        $this->_driver = (Co()->get(SimpleTokenBucket::class))($this->key(), $capacity, $qos);
        if($interval){
            $this->_driver->setTimestamp($interval);
        }
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
        return $this->_driver->get();
    }
}
<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal\Timer;

use CasualMan\Common\Internal\Cache;
use Workerman\Timer;

class Timers {

    /**
     * @var int
     */
    public static $capacity = 1000;

    /**
     * @var array [id => timer_id ...]
     */
    protected static $_timers;

    /**
     * @param string $id
     * @param $timerId
     */
    public static function addTimers(string $id, $timerId): void
    {
        self::$_timers[$id] = $timerId;
    }

    /**
     * @return array|null
     */
    public static function getTimers() : ?array
    {
        return self::$_timers;
    }

    /**
     * @param int|float $interval
     * @param callable $callable
     * @param array|null $args
     * @param bool $persistent
     * @param string|null $id
     * @return int|false
     */
    public static function add(
        $interval,
        callable $callable,
        ?array $args = [],
        bool $persistent = true,
        ?string $id = null
    ) : int
    {
        if(count(self::$_timers) > self::$capacity){
            throw new TimersException('Timer exceeded capacity.',-1);
        }
        if($res = Timer::add($interval,$callable,$args ?? [],$persistent)){
            if($persistent){
                self::addTimers($id ?? self::id(), $res);
            }
        }
        return $res;
    }

    /**
     * @param string $id
     * @return bool
     */
    public static function del(string $id) :bool
    {
        if(isset(self::$_timers[$id])){
            if($res = Timer::del(self::$_timers[$id])){
                unset(self::$_timers[$id]);
                return $res;
            }
        }
        return true;
    }

    /**
     * 递归清除
     */
    public static function clear() :void
    {
        foreach (self::$_timers as $id => $timerId){
            self::del($id);
        }
        if(self::$_timers){
            self::clear();
        }
    }

    /**
     * @return string
     */
    public static function id() :string
    {
        return Cache::id();
    }
}
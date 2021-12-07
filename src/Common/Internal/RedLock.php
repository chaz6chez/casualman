<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal;

class RedLock
{
    private $_retryDelay;
    private $_retryCount;
    private $_clockDriftFactor = 0.01;

    private $_quorum;

    private $_servers = [];
    private $_instances = [];

    public function __construct()
    {
        $this->_servers = C('redis.redlock.servers');
        $this->_retryDelay = C('redis.redlock.retry_delay', 200);
        $this->_retryCount = C('redis.redlock.retry_count', 3);
        $this->_quorum  = min(count($this->_servers), (count($this->_servers) / 2 + 1));
        $this->_initInstances();
    }

    public function __invoke() : RedLock
    {
        return Co()->get(RedLock::class);
    }

    public static function __callStatic($name, $arguments)
    {
        $obj = new self();
        if(!method_exists($obj, $name)){
            throw new \RuntimeException('method not found');
        }
        return ($obj)->{$name}(...$arguments);
    }

    private function _initInstances()
    {
        if (empty($this->instances)) {
            foreach ($this->_servers as $server) {
                $this->_instances[] = make(Redis::class, $server);
            }
        }
    }

    private function _lockInstance(string $resource, string $token, int $ttl): int
    {
        $n = 0;
        foreach ($this->_instances as $instance){
            if(
                $instance instanceof Redis and
                $instance->call() and
                $instance->set($resource, $token, ['NX', 'PX' => $ttl])
            ){
                $n ++;
            }
        }
        return $n;
    }

    private function _unlockInstance(string $resource, string $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        foreach ($this->_instances as $instance){
            if(
                $instance instanceof Redis and
                $instance->call()
            ){
                $instance->handler()->eval($script, [$resource, $token], 1);
            }
        }
    }

    public function lock(string $resource, int $ttl): array
    {
        $token = uniqid();
        $retry = $this->_retryCount;
        do {
            $startTime = microtime(true) * 1000;
            $n = $this->_lockInstance($resource, $token, $ttl);
            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->_clockDriftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            if (
                $n >= $this->_quorum and
                $validityTime > 0
            ) {
                return [
                    'validity' => $validityTime,
                    'resource' => $resource,
                    'token'    => $token,
                ];
            }
            $this->_unlockInstance($resource, $token);
            $delay = mt_rand(floor($this->_retryDelay / 2), $this->_retryDelay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);
        return [];
    }

    public function unlock(string $resource, string $token)
    {
        $this->_unlockInstance($resource, $token);
    }
}
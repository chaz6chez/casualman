<?php
declare(strict_types=1);

namespace CasualMan\Common\Internal;

abstract class Driver{
    /**
     * @var \Redis
     */
    protected $handler = null;
    protected $options = [];
    protected $tag;

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    abstract public function has(string $name) : bool;

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $default 默认值
     * @return mixed
     */
    abstract public function get(string $name, $default = false);

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param int|null $expire 有效时间 0为永久
     * @return bool
     */
    abstract public function set(string $name, $value, ?int $expire = null) : ?bool;

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    abstract public function inc(string $name, int $step = 1);

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string $name 缓存变量名
     * @param int $step 步长
     * @return false|int
     */
    abstract public function dec(string $name, int $step = 1);

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    abstract public function rm(string $name) : bool;

    /**
     * 清除缓存
     * @access public
     * @param string|null $tag 标签名
     * @return bool
     */
    abstract public function clear(?string $tag = null) : bool;

    /**
     * 检查连接
     * @access public
     * @return bool
     */
    abstract public function call() : bool;

    /**
     * 获取实际的缓存标识
     * @access public
     * @param string $name 缓存名
     * @return string
     */
    protected function getCacheKey(string $name) : string
    {
        return $this->options['prefix'] . $name;
    }

    /**
     * 读取缓存并删除
     * @access public
     * @param string $name 缓存变量名
     * @return mixed|void
     */
    public function pull(string $name) {
        $this->call();
        $result = $this->get($name, false);
        if ($result) {
            $this->rm($name);
            return $result;
        } else {
            return;
        }
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value 存储数据
     * @param int|null $expire 有效时间 0为永久
     * @return mixed
     * @throws \throwable
     */
    public function remember(string $name, $value, ?int $expire = null) {
        $this->call();
        if (!$this->has($name)) {
            $time = time();
            while ($time + 5 > time() && $this->has($name . '_lock')) {
                // 存在锁定则等待
                usleep(200000);
            }

            try {
                // 锁定
                $this->set($name . '_lock', true);
                if ($value instanceof \Closure) {
                    $value = call_user_func($value);
                }
                $this->set($name, $value, $expire);
                // 解锁
                $this->rm($name . '_lock');
            } catch (\Exception $e) {
                // 解锁
                $this->rm($name . '_lock');
                throw $e;
            } catch (\throwable $e) {
                $this->rm($name . '_lock');
                throw $e;
            }
        } else {
            $value = $this->get($name);
        }
        return $value;
    }

    /**
     * 缓存标签
     * @access public
     * @param string $name 标签名
     * @param string|array $keys 缓存标识
     * @param bool $overlay 是否覆盖
     * @return $this
     */
    public function tag(string $name, $keys = null, bool $overlay = false) : Driver
    {
        $this->call();
        if (is_null($name)) {

        } elseif (is_null($keys)) {
            $this->tag = $name;
        } else {
            $key = 'tag_' . md5($name);
            if (is_string($keys)) {
                $keys = explode(',', $keys);
            }
            $keys = array_map([$this, 'getCacheKey'], $keys);
            if ($overlay) {
                $value = $keys;
            } else {
                $value = array_unique(array_merge($this->getTagItem($name), $keys));
            }
            $this->set($key, implode(',', $value), 0);
        }
        return $this;
    }

    /**
     * 更新标签
     * @access public
     * @param string $name 缓存标识
     * @return void
     */
    protected function setTagItem(string $name) {
        $this->call();
        if ($this->tag) {
            $key = 'tag_' . md5($this->tag);
            $this->tag = null;
            if ($this->has($key)) {
                $value = explode(',', $this->get($key));
                $value[] = $name;
                $value = implode(',', array_unique($value));
            } else {
                $value = $name;
            }
            $this->set($key, $value, 0);
        }
    }

    /**
     * 获取标签包含的缓存标识
     * @access public
     * @param string $tag 缓存标签
     * @return array
     */
    protected function getTagItem(string $tag) : array {
        $key = 'tag_' . md5($tag);
        $value = $this->get($key);
        if ($value) {
            return array_filter(explode(',', $value));
        } else {
            return [];
        }
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     *
     * @access public
     * @return \Redis
     */
    public function handler() : \Redis
    {
        return $this->handler;
    }
}

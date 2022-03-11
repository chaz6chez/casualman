<?php
declare(strict_types=1);

namespace CasualMan\Package\RateLimit;

class SimpleTokenBucket extends AbstractTokenBucket
{
    protected $_timestamp;

    public function getTimestamp(): int
    {
        return $this->_timestamp ?? 1;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->_timestamp = $timestamp;
    }
}
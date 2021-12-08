<?php

declare(strict_types=1);

namespace CasualMan\Application\RateLimit;

use CasualMan\Common\Internal\RateLimit\AbstractRate;
use CasualMan\Common\Process\RpcServer;

class ClearingRateLimit extends AbstractRate
{
    public function key(): string
    {
        return strtolower(S_NAME) .':rate:' . RpcServer::jsonFormat()->method;
    }
}

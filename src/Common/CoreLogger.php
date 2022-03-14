<?php
declare(strict_types=1);

namespace CasualMan\Common;

use DateTimeZone;
use Monolog\Logger;

class CoreLogger extends Logger {

    public function __construct(array $handlers = [], array $processors = [], ?DateTimeZone $timezone = null)
    {
        parent::__construct('core', $handlers, $processors, $timezone);
    }
}
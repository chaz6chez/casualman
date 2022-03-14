<?php

use Monolog\Handler\StreamHandler;
use CasualMan\Common\CoreLogger;

/** @var CoreLogger $logger */
$logger = Co()->get(CoreLogger::class);

return $logger->setHandlers([
    make(StreamHandler::class, runtime_path() . "/{$logger->getName()}/error.log", CoreLogger::ERROR),
    make(StreamHandler::class, runtime_path() . "/{$logger->getName()}/warning.log", CoreLogger::WARNING),
    make(StreamHandler::class, runtime_path() . "/{$logger->getName()}/notice.log", CoreLogger::NOTICE),
]);
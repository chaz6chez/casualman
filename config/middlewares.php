<?php

return [
    '3Y-CLEARING-CENTER' => [
        DEBUG ? Co()->get(\CasualMan\Application\Middleware\DebugSQLMiddleware::class) : null,
        Co()->get(\CasualMan\Application\Middleware\RateLimitMiddleware::class),
        Co()->get(\CasualMan\Application\Middleware\ServerErrorCatchMiddleware::class),
    ]
];
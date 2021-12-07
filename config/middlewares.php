<?php

return [
    '3Y-CLEARING-CENTER' => [
        DEBUG ? Co()->get(\CasualMan\Clearing\Middleware\DebugSQLMiddleware::class) : null,
        Co()->get(\CasualMan\Clearing\Middleware\RateLimitMiddleware::class),
        Co()->get(\CasualMan\Clearing\Middleware\ServerErrorCatchMiddleware::class),
    ]
];
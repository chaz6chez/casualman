<?php
\Kernel\Router::addRoute('notice','demo', [
    new \CasualMan\Clearing\Controller\Demo() , 'demo']
)->middlewares([
    \CasualMan\Clearing\Middleware\DemoMid::class
]);

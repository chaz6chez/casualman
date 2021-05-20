<?php
\Kernel\Router::addRoute(['notice', 'normal'],'@demo', [
    new \CasualMan\Clearing\Controller\Demo() , 'demo']
)->middlewares([
    \CasualMan\Clearing\Middleware\DemoMid::class
]);

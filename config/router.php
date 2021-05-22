<?php
\Kernel\Router::addRoute(['notice', 'normal'],'@demo', [
    new \CasualMan\Demo\Controller\Demo() , 'demo']
)->middlewares([
    \CasualMan\Demo\Middleware\DemoMid::class
]);

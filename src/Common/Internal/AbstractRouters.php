<?php
declare(strict_types=1);
namespace CasualMan\Common\Internal;

use Kernel\Route;

abstract class AbstractRouters
{

    /**
     * @var Route[]
     */
    protected static $_routes;

    public static function add(Route $route) : Route
    {
        return self::$_routes[$route->getName()] = $route;
    }

    /**
     * @param Route ...$routes
     */
    public static function batch(Route ...$routes) : void
    {
        foreach ($routes as $route){
            self::add($route);
        }
    }

    /**
     * @param string[] $middlewares
     * @param bool $top
     */
    public static function middlewares(array $middlewares, bool $top = true) : void
    {
        foreach (self::$_routes as $route){
            $route->middlewares($middlewares, $top);
        }
    }

    abstract public static function register() : void;

}
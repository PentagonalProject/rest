<?php
namespace PentagonalProject\App\Rest\Abstracts;

use Slim\App;
use Slim\Interfaces\RouteGroupInterface;

/**
 * Class RouteMapperBaseAbstract
 * @package PentagonalProject\App\Rest\Abstracts
 *
 * @method RouteGroupInterface get(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface post(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface patch(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface head(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface put(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface delete(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface options(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface trace(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface view(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface purge(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface copy(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface lock(string $pattern, callable $callable, \Closure $routeCallback = null)
 * @method RouteGroupInterface unlock(string $pattern, callable $callable, \Closure $routeCallback = null)
 */
abstract class RouteMapperBaseAbstract
{
    /**
     * @var array
     */
    protected $lastRoutes = [];

    const AVAILABLE_METHODS = [
        'GET',
        'POST',
        'PUT',
        'HEAD',
        'PATCH',
        'DELETE',
        'OPTIONS',
        "VIEW",
        "PURGE",
        "COPY",
        'PURGE',
        'LOCK',
        'UNLOCK',
    ];

    /**
     * Slim App
     *
     * @return App
     */
    abstract protected function getApp() : App;

    /**
     * Group Pattern Base
     *
     * @return string
     */
    abstract protected function getGroupPattern() : string;

    /**
     * @param array $methods
     * @param string $pattern
     * @param callable $callback
     * @param \closure|null $routeCallback as route for as params
     *
     * @return RouteGroupInterface
     */
    public function map(
        array $methods,
        string $pattern,
        $callback,
        \closure $routeCallback = null
    ) : RouteGroupInterface {
        /**
         * @var App $app
         */
        $app = $this->getApp();
        $this->lastRoutes = func_get_args();
        $c =& $this;
        return $app->group(
            $this->getGroupPattern(),
            function () use ($c) {
                /**
                 * @var App $app
                 */
                $app = $this;
                $console = $c->lastRoutes;
                $result = $app->map(
                    $console[0],
                    $console[1],
                    $console[2]
                );
                if (isset($console[3]) && $console[3] instanceof \Closure) {
                    $console[3]($result, $c);
                }
            }
        );
    }

    /**
     * @param string $pattern
     * @param callable $callback
     * @param \closure|null $routeCallback
     *
     * @return mixed
     */
    public function any(
        string $pattern,
        $callback,
        \closure $routeCallback = null
    ) {
        if (is_array($callback) && count($callback) === 2) {
            $key = key($callback);
            if (isset($callback[$key])
                && is_string($callback[$key])
                && class_exists($callback[$key])
            ) {
                $reflection = new \ReflectionClass($callback[$key]);
                if ($reflection->isInstantiable()) {
                    $constructor = $reflection->getConstructor();
                    if (! $constructor
                         || $constructor->isPublic()
                            && $constructor->getNumberOfRequiredParameters() === 0
                    ) {
                        $callback[$key] = new $callback[$key]();
                    }
                }
            }
        }

        $args = func_get_args();
        array_unshift($args, self::AVAILABLE_METHODS);
        return call_user_func_array([$this, 'map'], $args);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        // add arguments
        array_unshift($arguments, [strtoupper($name)]);
        $result = call_user_func_array([$this, 'map'], $arguments);
        return $result;
    }
}

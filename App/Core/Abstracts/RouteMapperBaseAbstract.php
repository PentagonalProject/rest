<?php
namespace PentagonalProject\App\Rest\Abstracts;

use Psr\Container\ContainerInterface;
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
        // fix
        $this->callableFixLastRoute();
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
     * Fix Route Callable
     */
    private function callableFixLastRoute()
    {
        if (! is_array($this->lastRoutes[2]) || count($this->lastRoutes[2]) !== 2) {
            return;
        }

        $key = key($this->lastRoutes[2]);
        $class = reset($this->lastRoutes[2]);
        $method = next($this->lastRoutes[2]);
        if (is_string($class)
            && is_string($method)
            && class_exists($class)
            && method_exists($class, $method)
        ) {
            $refMethod = new \ReflectionMethod($class, $method);
            if ($refMethod->isStatic()) {
                return;
            }

            $reflection = new \ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                return;
            }

            $constructor = $reflection->getConstructor();
            if (! $constructor
                || $constructor->isPublic()
                && (
                    $constructor->getNumberOfRequiredParameters() === 0
                    || $constructor->getNumberOfRequiredParameters() === 1
                )
            ) {
                $container = $this->getApp()->getContainer();
                if ($constructor->getNumberOfRequiredParameters() === 1) {
                    $param = $constructor->getParameters()[0];
                    if ($param->allowsNull() || ! $param->hasType()
                        || ! $param->getType()
                        || is_subclass_of($param->getType()->getName(), ContainerInterface::class)
                    ) {
                        $this->lastRoutes[2][$key] = new $class($container);
                    }
                } else {
                    $this->lastRoutes[2][$key] = new $class();
                }
            }
        }
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

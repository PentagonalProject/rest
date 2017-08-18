<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author pentagonal <org@pentagonal.org>
 */

declare(strict_types=1);

namespace PentagonalProject\Modules\Recipicious\Lib;

use PentagonalProject\Modules\Recipicious\Recipicious;
use Slim\App;
use Slim\Interfaces\RouteGroupInterface;

/**
 * Class Api
 * @package PentagonalProject\Modules\Recipicious\Lib
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
class Api
{
    /**
     * @var Recipicious
     */
    protected $module;

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
     * Api constructor.
     *
     * @param Recipicious $module
     */
    public function __construct(Recipicious $module)
    {
        $this->module = $module;
    }

    /**
     * @return Recipicious
     */
    public function getModule() : Recipicious
    {
        return $this->module;
    }

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
        $app = $this->module->getContainer()->get('app');
        $this->lastRoutes = func_get_args();
        $c =& $this;
        return $app->group(
            $this->module->getGroupPattern(),
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

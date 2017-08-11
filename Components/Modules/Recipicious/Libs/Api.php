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

/**
 * Class Api
 * @package PentagonalProject\Modules\Recipicious\Lib
 */
class Api
{
	/**
	 * Lib module
	 *
	 * @var Recipicious $module
	 */
    private $module;

    public function __construct(Recipicious &$module)
    {
        $this->module = $module;
    }

	/**
	 * Api get route
	 *
	 * @param string   $group
	 * @param string   $pattern
	 * @param callable $callable
	 */
    public function get($group, $pattern, callable $callable)
    {
        $this->group($group, ['GET'], $pattern, $callable);
    }

	/**
	 * Api post route
	 *
	 * @param string   $group
	 * @param string   $pattern
	 * @param callable $callable
	 */
    public function post($group, $pattern, $callable)
    {
        $this->group($group, ['POST'], $pattern, $callable);
    }

	/**
	 * Api delete route
	 *
	 * @param string   $group
	 * @param string   $pattern
	 * @param callable $callable
	 */
    public function delete($group, $pattern, $callable)
    {
        $this->group($group, ['DELETE'], $pattern, $callable);
    }

	/**
	 * Group api route
	 *
	 * @param string   $name
	 * @param array    $methods
	 * @param string   $pattern
	 * @param callable $callable
	 */
    private function group($name, array $methods, $pattern, $callable)
    {
        /**
         * @var App $app
         */
        $app = $this->module->getContainer()['app'];
        $app->group(
            $name,
            function () use ($app, $methods, $pattern, $callable) {
                $app->map($methods, $pattern, $callable);
            }
        );
    }
}

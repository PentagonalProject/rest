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

namespace PentagonalProject\App\Rest\Util;

use PentagonalProject\App\Rest\Record\AppFacade;
use Psr\Container\ContainerInterface;

/**
 * Class Facade
 * @package PentagonalProject\App\Rest\Util
 */
class Facade
{
    /**
     * @param string $name
     * @param \Closure $closure
     * @param string|null $appName
     * @return ContainerInterface
     */
    public static function put(string $name, \Closure $closure, string $appName = null) : ContainerInterface
    {
        /**
         * @var ContainerInterface $container
         */
        $container = self::containerRollBackSwitch(false, $appName?: AppFacade::current()->getName());
        $container[$name] = $closure;
        return $container;
    }

    /**
     * @param string $name
     * @param \Closure $closure
     * @param string $appName
     * @return ContainerInterface
     */
    public static function putSwitch(string $name, \Closure $closure, string $appName) : ContainerInterface
    {
        /**
         * @var ContainerInterface $container
         */
        $container = self::containerRollBackSwitch(true, $appName?: AppFacade::current()->getName());
        $container[$name] = $closure;
        return $container;
    }

    /**
     * @param bool $switch
     * @param string|null $appName
     * @return ContainerInterface
     */
    private static function containerRollBackSwitch(bool $switch, string $appName = null) : ContainerInterface
    {
        $current = AppFacade::current()->getName();
        if (!$appName || $current == $appName) {
            $container = AppFacade::current()->getAccessor()->getContainer();
        } else {
            $container = AppFacade::switchTo($appName)->getAccessor()->getContainer();
            ! $switch && AppFacade::switchTo($current);
        }

        return $container;
    }

    /**
     * @param string $name
     * @param string|null $appName
     * @return mixed
     */
    public static function get(string $name, string $appName = null)
    {
        return self::containerRollBackSwitch(false, $appName?: AppFacade::current()->getName())->get($name);
    }

    /**
     * @param string $name
     * @param string $appName
     * @return mixed
     */
    public static function getSwitch(string $name, string $appName)
    {
        return self::containerRollBackSwitch(true, $appName?: AppFacade::current()->getName())->get($name);
    }
}

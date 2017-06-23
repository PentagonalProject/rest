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

namespace PentagonalProject\App\Rest\Record;

use PentagonalProject\App\Rest\Exceptions\FileNotFoundException;
use Psr\Log\InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class Facade
 * @package PentagonalProject\App\Rest\Record
 */
class Facade
{
    /**
     * @var Facade[]
     */
    protected static $routines = [];

    /**
     * @var string
     */
    protected static $current;

    /**
     * @var string
     */
    protected $applicationName;

    /**
     * @var FacadeAccessor
     */
    protected $containerAccessor;

    /**
     * @var string
     */
    protected $webRootPath;

    /**
     * Facade constructor.
     * @param string $appName
     * @internal
     */
    private function __construct(string $appName)
    {
        if (isset(self::$routines[$appName])) {
            throw new RuntimeException(
                sprintf(
                    "Instance Application with %s is exists!",
                    $appName
                ),
                E_USER_ERROR
            );
        }

        self::$routines[$appName] =& $this->createObjectAccessor($appName);
    }

    /**
     * Create Object Accessor
     *
     * @param string $appName
     * @return Facade
     */
    protected function &createObjectAccessor(string $appName) : Facade
    {
        $this->applicationName = $appName;
        $this->containerAccessor = new FacadeAccessor($this);

        return $this;
    }

    /**
     * Get Application Name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->applicationName;
    }

    /**
     * Get Accessor
     *
     * @return FacadeAccessor
     */
    public function getAccessor() : FacadeAccessor
    {
        return $this->containerAccessor;
    }

    /**
     * @param string $appName
     * @return bool
     */
    public static function has(string $appName) : bool
    {
        return isset(self::$routines[$appName]);
    }

    /**
     * Create Instance
     *
     * @param string $appName
     * @return Facade
     */
    public static function register(string $appName) : Facade
    {
        return new static($appName);
    }

    /**
     * @param string $appName
     * @return Facade
     */
    public static function switchTo(string $appName) : Facade
    {
        if (!static::has($appName)) {
            throw new \UnexpectedValueException(
                'Application %s does not exists!',
                E_USER_ERROR
            );
        }

        self::$current = $appName;
        return self::$routines[$appName];
    }

    /**
     * Get Current Facade
     *
     * @return Facade
     * @throws UnexpectedValueException
     */
    public static function current() : Facade
    {
        if (self::$current) {
            return self::switchTo(self::$current);
        }
        throw new UnexpectedValueException(
            'Can not determine current facade!',
            E_COMPILE_ERROR
        );
    }

    /**
     * Get Count Routines
     *
     * @return int
     */
    public static function countAccessor() : int
    {
        return count(static::$routines);
    }

    /**
     * Get order Number
     *
     * @return int
     */
    public function currentOrderNumber() : int
    {
        return array_search($this->getName(), array_keys(self::$routines), true);
    }

    /**
     * Include Scope
     *
     * @param-read string $file
     * @return mixed
     * @throws FileNotFoundException
     */
    public static function includeScope()
    {
        if (func_num_args() < 1) {
            throw new InvalidArgumentException(
                'Argument 1 could not be empty.',
                E_USER_ERROR
            );
        }

        if (!is_string(func_get_arg(0))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 must be as a string %s given.',
                    gettype(func_get_arg(0))
                ),
                E_USER_ERROR
            );
        }

        if (!($path = stream_resolve_include_path(func_get_arg(0)))) {
            throw new FileNotFoundException(
                func_get_arg(0)
            );
        }

        /**
         * closure include of scope to prevent access @uses Application
         * bind to @uses Arguments
         * if inside of include call $this it wil be access as @uses Arguments object
         */
        $args = new Arguments(func_get_args());
        $fn = (function ($file) {
            /** @noinspection PhpIncludeInspection */
            return include $file;
        })->bindTo($args);

        return $fn($args[0]);
    }
}
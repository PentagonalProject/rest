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
 * Class AppFacade
 * @package PentagonalProject\App\Rest\Record
 */
class AppFacade
{
    /**
     * @var AppFacade[]
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
     * @var AppFacadeAccessor
     */
    protected $containerAccessor;

    /**
     * @var Arguments
     */
    protected $arguments;

    /**
     * AppFacade constructor.
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

        self::$current = $appName;
        self::$routines[$appName] =& $this->createObjectAccessor($appName);
        // set arguments Cached
        $this->arguments = new Arguments();
    }

    /**
     * Create Object Accessor
     *
     * @param string $appName
     * @return AppFacade
     */
    protected function &createObjectAccessor(string $appName) : AppFacade
    {
        $this->applicationName = $appName;
        $this->containerAccessor = new AppFacadeAccessor($this);

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
     * @return AppFacadeAccessor
     */
    public function getAccessor() : AppFacadeAccessor
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
     * @return AppFacade
     */
    public static function register(string $appName) : AppFacade
    {
        return new static($appName);
    }

    /**
     * @param string $appName
     * @return AppFacade
     * @throws \UnexpectedValueException
     */
    public static function switchTo(string $appName) : AppFacade
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
     * @return AppFacadeAccessor
     */
    public static function getCurrentAccessor() : AppFacadeAccessor
    {
        return self::current()->getAccessor();
    }

    /**
     * Getting Current Accessor instance container Application
     *
     * @param string $name
     *
     * @return mixed
     */
    public static function access(string $name)
    {
        return self::getCurrentAccessor()->getContainer()[$name];
    }

    /**
     * Get Current AppFacade
     *
     * @return AppFacade
     * @throws UnexpectedValueException
     */
    public static function current() : AppFacade
    {
        if (self::$current) {
            return self::$routines[self::$current];
        }

        throw new UnexpectedValueException(
            'Can not determine current application from facade!',
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
     * Include Scope Binding Object
     *
     * @param-read string $file
     * @return mixed
     * @throws FileNotFoundException
     */
    public static function includeScopeBind()
    {
        /**
         * closure include of scope to prevent access
         * bind to @uses Object arguments
         * if inside of include call $this it wil be access as @uses Arguments object
         */
        $args = self::validateScope(func_get_args());
        return \Closure::bind(
            function ($file) {
                /** @noinspection PhpIncludeInspection */
                return include $file;
            },
            !is_object($args[1]) ? null : $args[1]
        )($args[0]);
    }

    /**
     * @param string $file
     * Binding To Slim Application
     *
     * @return mixed
     */
    public function includeBindToSlim(string $file)
    {
        $app = $this->getAccessor()->getApp();
        return $this->includeScopeBind($file, $app);
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
        /**
         * closure include of scope to prevent access
         * bind to @uses Arguments
         * if inside of include call $this it wil be access as @uses Arguments object
         */
        $args = self::validateScope(func_get_args());
        return self::includeScopeBind($args[0], $args);
    }

    /**
     * Include Scope Once
     *
     * @param-read string $file
     * @return mixed
     * @throws FileNotFoundException
     */
    public static function includeScopeOnce()
    {
        /**
         * closure include of scope to prevent access
         * bind to @uses Arguments
         * if inside of include call $this it wil be access as @uses Arguments object
         */
        $args = self::validateScope(func_get_args());
        return \Closure::bind(
            function ($file) {
                /** @noinspection PhpIncludeInspection */
                return include_once $file;
            },
            $args
        )($args[0]);
    }

    /**
     * @param array $args
     * @return Arguments
     * @throws FileNotFoundException
     */
    private static function validateScope(array $args) : Arguments
    {
        if (count($args) < 1) {
            throw new InvalidArgumentException(
                'Argument 1 could not be empty.',
                E_USER_ERROR
            );
        }

        if (!is_string(reset($args))) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 must be as a string %s given.',
                    gettype(reset($args))
                ),
                E_USER_ERROR
            );
        }

        if (!($path = stream_resolve_include_path(reset($args)))) {
            throw new FileNotFoundException(
                reset($args)
            );
        }

        return new Arguments($args);
    }

    /**
     * @return AppFacade[]
     */
    public static function getRoutines() : array
    {
        return self::$routines;
    }

    /**
     * @return array
     */
    public static function getAppList() : array
    {
        return array_keys(self::$routines);
    }

    /**
     * @return Arguments
     */
    public function getArguments() : Arguments
    {
        return $this->arguments;
    }

    /**
     * Set Arguments
     *
     * @param mixed $key
     * @param $value
     * @return AppFacade
     */
    public function setArgument($key, $value) : AppFacade
    {
        $this->arguments->set($key, $value);
        return $this;
    }

    /**
     * Get Key From Arguments
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function getArgument($key, $default = null)
    {
        return $this->arguments->get($key, $default);
    }

    /**
     * Check if Has Arguments
     *
     * @param mixed $key
     * @return bool
     */
    public function hasArgument($key) : bool
    {
        return $this->arguments->has($key);
    }

    /**
     * @param mixed $key
     * @return AppFacade
     */
    public function removeArgument($key) : AppFacade
    {
        $this->arguments->remove($key);

        return $this;
    }

    /**
     * Replace Argument
     *
     * @param array $args
     * @return AppFacade
     */
    public function replaceArguments(array $args) : AppFacade
    {
        foreach ($args as $key => $value) {
            $this->setArgument($key, $value);
        }

        return $this;
    }
}

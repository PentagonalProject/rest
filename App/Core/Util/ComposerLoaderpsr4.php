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

namespace PentagonalProject\App\Rest\Util;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;

/**
 * Class ComposerLoaderPSR4
 * @package PentagonalProject\App\Rest\Util
 */
class ComposerLoaderPSR4
{
    /**
     * @var ClassLoader
     */
    protected $classLoader;

    /**
     * ComposerLoaderPSR4 constructor.
     * @param ClassLoader|null $classLoader
     */
    public function __construct(ClassLoader $classLoader = null)
    {
        $this->classLoader = $classLoader ?: new ClassLoader();
    }

    /**
     * Add PSR4
     *
     * @param array $nameSpacePath key as NameSpace & value as paths
     * @return ComposerLoaderPSR4
     * @throws InvalidArgumentException
     */
    public function addArray(array $nameSpacePath) : ComposerLoaderPSR4
    {
        foreach ($nameSpacePath as $nameSpace => $paths) {
            // if there was has a slash fix to backslash
            if (strpos($nameSpace, '/') !== false) {
                $nameSpace = preg_replace('(\\\|\/)', '\\', $nameSpace);
            }
            // Trim & add su-fix back slash that make Name Space Valid
            $nameSpace = trim($nameSpace, '\\') . '\\';
            if (!is_string($paths) && ! is_array($paths)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Invalid paths for Name Space %s. Paths must be as a string or array.",
                        $nameSpace
                    ),
                    E_USER_ERROR
                );
            } elseif (is_array($paths)) {
                foreach ($paths as $keyPath => $path) {
                    if (!is_string($path)) {
                        throw new InvalidArgumentException(
                            sprintf(
                                "Invalid path value for Name Space %s in key %s. Path must be as a string.",
                                $nameSpace,
                                $keyPath
                            ),
                            E_USER_ERROR
                        );
                    }
                }
            }

            $this->classLoader->addPsr4(
                $nameSpace,
                (array) $paths
            );
        }

        return $this;
    }

    /**
     * Add Path
     *
     * @param string       $prefix
     * @param string|array $paths
     * @return ComposerLoaderPSR4
     */
    public function add(string $prefix, $paths) : ComposerLoaderPSR4
    {
        return $this->addArray([$prefix => $paths]);
    }

    /**
     * Register Auto loader
     *
     * @param bool $prepend
     */
    public function register($prepend = false)
    {
        $this->classLoader->register($prepend);
    }

    /**
     * UnRegister The Loader
     */
    public function unRegister()
    {
        $this->classLoader->unregister();
    }

    /**
     * @param array $path
     * @param ClassLoader|null $classLoader
     * @return ComposerLoaderPSR4
     */
    public static function create(array $path, ClassLoader $classLoader = null) : ComposerLoaderPSR4
    {
        $object = new static($classLoader ?: new ClassLoader());
        $object->addArray($path);
        return $object;
    }
}

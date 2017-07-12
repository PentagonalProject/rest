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

namespace PentagonalProject\App\Rest\Abstracts;

use PentagonalProject\App\Rest\Interfaces\ModularInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Class ModularAbstract
 * @package PentagonalProject\App\Rest\Abstracts
 */
abstract class ModularAbstract implements ModularInterface
{
    /**
     * @var string
     */
    private $module_name_selector;

    /**
     * Modular Name
     *
     * @var string
     */
    protected $modular_name = '';

    /**
     * Modular URL
     *
     * @var string
     */
    protected $modular_uri = '';

    /**
     * Modular Version
     *
     * @var mixed
     */
    protected $modular_version = '';

    /**
     * Modular Author Name
     *
     * @var string
     */
    protected $modular_author = '';

    /**
     * Modular Author URL
     *
     * @var string
     */
    protected $modular_author_uri = '';

    /**
     * Modular Description
     *
     * @var string
     */
    protected $modular_description = '';

    /**
     * @var ContainerInterface
     * @final
     */
    private $modular_container;

    /**
     * @var ReflectionClass
     */
    private $privateModularReflectionClass;

    /**
     * Modular constructor.
     * @param ContainerInterface $container
     * @param string $moduleNameSelector
     * @final as prevent to inheritance
     */
    final public function __construct(ContainerInterface $container, string  $moduleNameSelector)
    {
        $this->modular_container = $container;
        $this->module_name_selector = $moduleNameSelector;
        $this->getModularName();
    }

    /**
     * Get Selector Of Module
     *
     * @return string
     */
    final public function getModularNameSelector() : string
    {
        return $this->module_name_selector;
    }

    /**
     * Retrieve Container
     *
     * @return ContainerInterface
     */
    final public function getContainer() : ContainerInterface
    {
        return $this->modular_container;
    }

    /**
     * Get Modular Info
     *
     * @return array
     */
    public function getModularInfo() : array
    {
        return [
            static::NAME        => $this->getModularName(),
            static::VERSION     => $this->getModularVersion(),
            static::URI         => $this->getModularUri(),
            static::AUTHOR      => $this->getModularAuthor(),
            static::AUTHOR_URI  => $this->getModularAuthorUri(),
            static::DESCRIPTION => $this->getModularDescription(),
            static::CLASS_NAME  => get_class($this),
            static::FILE_PATH   => $this->getModularRealPath(),
            static::SELECTOR    => $this->getModularNameSelector(),
        ];
    }

    /**
     * Get Reflection
     *
     * @return ReflectionClass
     */
    final protected function getModularReflection() : ReflectionClass
    {
        if (! $this->privateModularReflectionClass instanceof ReflectionClass) {
            $this->privateModularReflectionClass = new ReflectionClass($this);
        }

        return $this->privateModularReflectionClass;
    }

    /**
     * Get Path
     *
     * @return string
     */
    final public function getModularRealPath() : string
    {
        return $this->getModularReflection()->getFileName();
    }

    /**
     * Get Name Space
     *
     * @return string
     */
    final public function getModularNameSpace() : string
    {
        return $this->getModularReflection()->getNamespaceName();
    }

    /**
     * Get ShortName of Class
     *
     * @return string
     */
    final public function getModularShortName() : string
    {
        return $this->getModularReflection()->getShortName();
    }

    /**
     * {@inheritdoc}
     */
    public function getModularName() : string
    {
        if (!is_string($this->modular_name)
            || trim($this->modular_name) == ''
        ) {
            $this->modular_name = $this->getModularReflection()->getName();
        }

        return (string) $this->modular_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularVersion() : string
    {
        return (string) $this->modular_version;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularAuthor() : string
    {
        return (string) $this->modular_author;
    }

    /**
     *{@inheritdoc}
     */
    public function getModularAuthorUri() : string
    {
        return (string) $this->modular_author_uri;
    }

    /**
     * Get Modular URL
     *
     * @return string
     */
    public function getModularUri() : string
    {
        return (string) $this->modular_uri;
    }

    /**
     *{@inheritdoc}
     */
    public function getModularDescription() : string
    {
        return (string) $this->modular_description;
    }
}

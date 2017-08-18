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

namespace PentagonalProject\Tests\PhpUnit\Core;

use PentagonalProject\App\Rest\Abstracts\ContainerAccessor;
use Psr\Container\ContainerInterface;
use Slim\Container;

/**
 * Class AbstractsTest
 * @package PentagonalProject\Tests\PhpUnit\Core
 */
class AbstractsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->container = new \Slim\Container([
            'test' => function($c) {
                return $c;
            }
        ]);
    }

    public function testContainerAccessor()
    {
        $class = new class($this->container) extends ContainerAccessor {
            public function __construct(ContainerInterface $container)
            {
                $this->setContainerAggregate($container);
            }
        };

        $this->assertInstanceOf(
            \ArrayAccess::class,
            $class,
            sprintf(
                '%1$s instance of %2$s',
                ContainerAccessor::class,
                \ArrayAccess::class
            )
        );

        $this->assertInstanceOf(
            ContainerInterface::class,
            $class->getContainer(),
            sprintf(
                '%1$s instance of %2$s',
                ContainerAccessor::class .'::getContainer()',
                ContainerInterface::class
            )
        );

        $this->assertAttributeSame(
            $this->container,
            'container',
            $class
        );

        $this->assertInstanceOf(
            ContainerInterface::class,
            $class['test'],
            sprintf(
                '%1$s instance of %2$s internal return value fallback.',
                Container::class .'::offsetGet("test")',
                ContainerInterface::class
            )
        );
    }
}

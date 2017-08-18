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

use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Abstracts\ContainerAccessorAbstracts;
use PentagonalProject\App\Rest\Abstracts\ModelValidatorAbstract;
use PentagonalProject\App\Rest\Traits\ModelValidatorTrait;
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

    /**
     * @var \Closure
     */
    protected $fallBackClosure;

    /**
     * AbstractsTest constructor.
     *
     * {@inheritdoc}
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->fallBackClosure = function ($c) {
                return $c;
        };

        $this->container = new Container([
            'test' => $this->fallBackClosure
        ]);
    }

    public function testContainerAccessor()
    {
        $class = new class($this->container) extends ContainerAccessorAbstracts {
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
                ContainerAccessorAbstracts::class,
                \ArrayAccess::class
            )
        );

        $this->assertInstanceOf(
            ContainerInterface::class,
            $class->getContainer(),
            sprintf(
                '%1$s instance of %2$s',
                ContainerAccessorAbstracts::class . '::getContainer()',
                ContainerInterface::class
            )
        );

        $this->assertAttributeSame(
            $this->container,
            'container',
            $class
        );

        $this->assertNotSame(
            $class['test'],
            $this->fallBackClosure,
            'Object container array access is a return value of closure'
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

    public function testModelValidator()
    {
        $class = new class() extends ModelValidatorAbstract {
            use ModelValidatorTrait;

            protected function toCheck(): array
            {
                // make values invalid
                return [
                    'test' => ['between' => [5, 6]],
                    'test2' => ['more' => 5],
                    'test3' => ['less' => 6],
                ];
            }

            public function getValuesToCheck()
            {
                return $this->toCheck();
            }

            public function lengthMustBeLessThanAlt(
                string $attribute,
                int $length
            ) {
                $this->lengthMustBeLessThan($attribute, $length);
            }
            public function lengthMustBeMoreThanAlt(
                string $attribute,
                int $length
            ) {
                $this->lengthMustBeMoreThan($attribute, $length);
            }

            public function lengthMustBeBetweenAlt(
                string $attribute,
                int $min,
                int $max
            ) {
                $this->lengthMustBeBetween($attribute, $min, $max);
            }

            public function run()
            {
                return $this;
            }
        };

        $class = $class::check(new CollectionFetch(
            [
                // set as invalid
                'test' => '1234',
                'test2' => '12345',
                'test3' => '123456',
            ]
        ));
        /** @noinspection PhpUndefinedMethodInspection */
        foreach ($class->getValuesToCheck() as $key => $value) {
            foreach ($value as $keyName => $realValue) {
                try {
                    switch ($keyName) {
                        case 'between':
                            /** @noinspection PhpUndefinedMethodInspection */
                            $class->lengthMustBeBetweenAlt(
                                $keyName,
                                reset($realValue),
                                next($realValue)
                            );
                            break;
                        case 'more':
                            /** @noinspection PhpUndefinedMethodInspection */
                            $class->lengthMustBeMoreThanAlt($keyName, $realValue);
                            break;
                        case 'less':
                            /** @noinspection PhpUndefinedMethodInspection */
                            $class->lengthMustBeLessThanAlt($keyName, $realValue);
                            break;
                    }
                } catch (\Throwable $exception) {
                    $this->assertInstanceOf(
                        \LengthException::class,
                        $exception
                    );
                }
            }
        }
    }
}

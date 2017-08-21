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

namespace PentagonalProject\Tests\PhpUnit\App\Core;

use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Abstracts\ContainerAccessorAbstract;
use PentagonalProject\App\Rest\Abstracts\ModelValidatorAbstract;
use PentagonalProject\App\Rest\Abstracts\ModularAbstract;
use PentagonalProject\App\Rest\Interfaces\ModularInterface;
use PentagonalProject\Tests\PhpUnit\App\Core\ResourceAdditions\ModelValidator;
use PentagonalProject\Tests\PhpUnit\App\Core\ResourceAdditions\ModuleModular;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Container;

/**
 * Class AbstractsTest
 * @package PentagonalProject\Tests\PhpUnit\App\Core
 */
class AbstractsTest extends TestCase
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
        $class = new class($this->container) extends ContainerAccessorAbstract {
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
                ContainerAccessorAbstract::class,
                \ArrayAccess::class
            )
        );

        $this->assertInstanceOf(
            ContainerInterface::class,
            $class->getContainer(),
            sprintf(
                '%1$s instance of %2$s',
                ContainerAccessorAbstract::class . '::getContainer()',
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
        /**
         * @var @anonymous
         */
        $class = ModelValidator::check(new CollectionFetch(
            [
                // set as invalid
                'test' => '1234',
                'test2' => '12345',
                'test3' => '123456',
                'test4' => false,
                'test5' => [],
                'test6' => '1234567',
            ]
        ));

        $this->assertInstanceOf(
            ModelValidatorAbstract::class,
            $class
        );
        $this->assertNotEmpty($class->toCheck());
    }

    public function testModular()
    {
        $module = new ModuleModular($this->container, 'anonymous');
        $this->assertInstanceOf(
            ModularInterface::class,
            $module,
            sprintf(
                '%1$s instance of %2$s',
                ModularAbstract::class,
                ModularInterface::class
            )
        );

        // init
        $this->assertInstanceOf(
            ModularAbstract::class,
            $module->init(),
            sprintf(
                '%1$s instance of %2$s',
                ModularAbstract::class,
                ModularInterface::class
            )
        );

        $this->assertInstanceOf(
            ContainerInterface::class,
            $module->getContainer(),
            sprintf(
                '%1$s instance of %2$s',
                ModularAbstract::class . '::getContainer()',
                ContainerInterface::class
            )
        );

        $this->assertInstanceOf(
            \ReflectionClass::class,
            $module->getReflection()
        );

        $this->assertStringStartsWith(
            '1',
            $module->getModularVersion(),
            'Modular Version as a string'
        );
        $this->assertStringStartsWith(
            'anonymous',
            $module->getModularNameSelector(),
            'Modular Selector as a string'
        );
        $this->assertEquals(
            'anonymous',
            $module->getModularNameSelector(),
            'Modular Selector as a string must be anonymous'
        );
        $this->assertStringStartsWith(
            $module->getReflection()->getName(),
            $module->getModularName(),
            'Modular Name must be as a string'
        );
        $this->assertNotEmpty(
            $module->getModularInfo(),
            'Modular ::getModularInfo() must be not empty'
        );
        $this->assertSame(
            $module->getModularRealPath(),
            __DIR__ . DIRECTORY_SEPARATOR
            . 'ResourceAdditions' . DIRECTORY_SEPARATOR
            .'ModuleModular.php',
            'Module RealPath must be set as current file'
        );
        $this->assertStringEndsWith(
            'Description',
            $module->getModularDescription(),
            'Modular Description as a string'
        );
        $this->assertStringStartsWith(
            'http',
            $module->getModularAuthorUri(),
            'Modular Author URI as a string'
        );
        $this->assertStringStartsWith(
            'http',
            $module->getModularUri(),
            'Modular URI as a string'
        );
        $this->assertStringStartsWith(
            'pentagonal',
            $module->getModularAuthor(),
            'Modular Author as a string'
        );

        $this->assertStringStartsWith(
            __NAMESPACE__,
            $module->getModularNameSpace(),
            'Modular Name Space as a string'
        );
        $this->assertStringStartsWith(
            'ModuleModular',
            $module->getModularShortName(),
            'Modular Short Name as a string'
        );
        $this->assertSame(
            ModularAbstract::NAME,
            'name'
        );
        $this->assertSame(
            ModularAbstract::SELECTOR,
            'modular_selector'
        );
        $this->assertSame(
            ModularAbstract::FILE_PATH,
            'file_path'
        );
        $this->assertSame(
            ModularAbstract::CLASS_NAME,
            'class_name'
        );
        $this->assertSame(
            ModularAbstract::DESCRIPTION,
            'description'
        );
        $this->assertSame(
            ModularAbstract::AUTHOR_URI,
            'author_uri'
        );
        $this->assertSame(
            ModularAbstract::AUTHOR,
            'author'
        );
        $this->assertSame(
            ModularAbstract::URI,
            'uri'
        );
        $this->assertSame(
            ModularAbstract::VERSION,
            'version'
        );
        $this->assertArrayHasKey(
            ModularAbstract::NAME,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::AUTHOR,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::VERSION,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::URI,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::AUTHOR_URI,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::DESCRIPTION,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::CLASS_NAME,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::FILE_PATH,
            $module->getModularInfo()
        );
        $this->assertArrayHasKey(
            ModularAbstract::SELECTOR,
            $module->getModularInfo()
        );
    }
}

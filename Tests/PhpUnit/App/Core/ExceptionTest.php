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

use PentagonalProject\App\Rest\Exceptions\EmptyFileException;
use PentagonalProject\App\Rest\Exceptions\FileNotFoundException;
use PentagonalProject\App\Rest\Exceptions\InvalidModularException;
use PentagonalProject\App\Rest\Exceptions\InvalidPathException;
use PentagonalProject\App\Rest\Exceptions\ModularNotFoundException;
use PentagonalProject\App\Rest\Exceptions\StreamConnectionException;
use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PHPUnit\Framework\TestCase;

/**
 * Class ExceptionTest
 * @package PentagonalProject\Tests\PhpUnit\App\Core
 */
class ExceptionTest extends TestCase
{

    public function testInvalidPathException()
    {
        $exception = new InvalidPathException(__FILE__, 'Test');
        $this->assertInstanceOf(
            \Exception::class,
            $exception,
            sprintf(
                '%1$s instance of %2$s',
                InvalidPathException::class,
                \Exception::class
            )
        );
        $this->assertSame(
            $exception->getPath(),
            __FILE__
        );
        $this->assertSame(
            $exception->getMessage(),
            'Test'
        );
        $this->assertEquals(
            $exception->getCode(),
            0
        );
    }

    public function testEmptyFileException()
    {
        $this->assertInstanceOf(
            InvalidPathException::class,
            new EmptyFileException(__FILE__),
            sprintf(
                '%1$s instance of %2$s',
                EmptyFileException::class,
                InvalidPathException::class
            )
        );
    }

    public function testFileNotFoundException()
    {
        $this->assertInstanceOf(
            InvalidPathException::class,
            new FileNotFoundException(__FILE__),
            sprintf(
                '%1$s instance of %2$s',
                FileNotFoundException::class,
                InvalidPathException::class
            )
        );
    }

    public function testInvalidModularException()
    {
        $this->assertInstanceOf(
            \Exception::class,
            new InvalidModularException(__FILE__),
            sprintf(
                '%1$s instance of %2$s',
                InvalidModularException::class,
                \Exception::class
            )
        );
    }

    public function testModularNotFoundException()
    {
        $this->assertInstanceOf(
            \Exception::class,
            new ModularNotFoundException(__FILE__),
            sprintf(
                '%1$s instance of %2$s',
                ModularNotFoundException::class,
                InvalidPathException::class
            )
        );
    }

    public function testStreamConnectionException()
    {
        $this->assertInstanceOf(
            \Exception::class,
            new StreamConnectionException(),
            sprintf(
                '%1$s instance of %2$s',
                StreamConnectionException::class,
                \RuntimeException::class
            )
        );
    }

    public function testUnauthorizedException()
    {
        $this->assertInstanceOf(
            \Exception::class,
            new UnauthorizedException(),
            sprintf(
                '%1$s instance of %2$s',
                UnauthorizedException::class,
                \ErrorException::class
            )
        );
    }
}

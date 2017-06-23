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

namespace PentagonalProject\App\Rest\Web\Component\Override;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\PhpError as SlimPhpError;
use Throwable;

/**
 * Class PhpError
 * @package PentagonalProject\App\Rest\Record
 */
class PhpError extends SlimPhpError
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PhpError constructor.
     * @param bool $displayErrorDetails
     * @param ContainerInterface|null $container
     */
    public function __construct($displayErrorDetails = false, ContainerInterface $container = null)
    {
        $this->container = $container;
        parent::__construct($displayErrorDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $error
    ) {
        if ($this->container instanceof ContainerInterface
            && $this->container->has('log')
        ) {
            /** @var Logger $log */
            $log = $this->container['log'];
            $log->error(
                $error->getMessage(),
                [
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'line' => $error->getLine()
                ]
            );
        }

        return parent::__invoke($request, $response, $error);
    }
}

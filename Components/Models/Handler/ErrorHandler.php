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

namespace PentagonalProject\Model\Handler;

use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\PhpError;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;

/**
 * Class ErrorHandler
 * @package PentagonalProject\Model\Handler
 */
class ErrorHandler extends PhpError
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ErrorHandler constructor.
     * @param bool $displayErrorDetails
     * @param ContainerInterface|null $container
     */
    public function __construct($displayErrorDetails = false, ContainerInterface $container = null)
    {
        $this->container = $container;
        parent::__construct($displayErrorDetails);
        if ($container && isset($container['whoops'])) {
            /**
             * @var Run $whoops
             */
            $whoops = $this->container['whoops'];
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Throwable $exception
     *
     * @return ResponseInterface|static
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Throwable $exception)
    {
        if ($this->displayErrorDetails
            && $this->container instanceof ContainerInterface
            && $this->container->has('whoops')
        ) {
            /**
             * @var Run $whoops
             */
            $whoops = $this->container['whoops'];
            $this->writeToErrorLog($exception);
            $contentType = $this->determineContentType($request);
            $this->pushHandlerByContentType($contentType);
            $output = $whoops->handleException($exception);
            $body = $response->getBody();
            $body->write($output);
            return $response
                ->withStatus(500)
                ->withHeader('Content-type', $contentType)
                ->withBody($body);
        }

        return parent::__invoke($request, $response, $exception);
    }

    /**
     * @param $contentType
     */
    protected function pushHandlerByContentType($contentType)
    {
        if ($this->container instanceof ContainerInterface
            && $this->container->has('whoops')
        ) {
            $contentTypeBasedHandler = null;
            switch ($contentType) {
                case 'application/json':
                    $contentTypeBasedHandler = new JsonResponseHandler();
                    break;
                case 'text/xml':
                case 'application/xml':
                    $contentTypeBasedHandler = new XmlResponseHandler();
                    break;
                case 'text/html':
                    $contentTypeBasedHandler = new PrettyPageHandler();
                    break;
                default:
                    return;
            }

            /**
             * @var Run $whoops
             */
            $whoops = $this->container['whoops'];
            $existingHandlers = array_merge([$contentTypeBasedHandler], $whoops->getHandlers());
            $whoops->clearHandlers();
            foreach ($existingHandlers as $existingHandler) {
                $whoops->pushHandler($existingHandler);
            }
        }
    }

    /**
     * @param \Exception|\Throwable $throwable
     */
    protected function writeToErrorLog($throwable)
    {
        if ($this->container instanceof ContainerInterface
            && $this->container->has('log')
        ) {
            /** @var Logger $log */
            $log = $this->container['log'];
            $message  = $throwable->getMessage();
            $message .= " ({$throwable->getFile()}:{$throwable->getLine()}:{$throwable->getCode()})";
            $log->error(
                $message,
                [
                    'exceptions' => $throwable
                ]
            );

            return;
        }

        parent::writeToErrorLog($throwable);
    }
}

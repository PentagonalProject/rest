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

namespace {

    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\App\Rest\Util\Hook;
    use PentagonalProject\Model\Handler\PhpError;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Slim\Handlers\AbstractHandler;
    use Slim\Handlers\NotAllowed;
    use Slim\Handlers\NotFound;
    use Slim\MiddlewareAwareTrait;

    if (! isset($this) || ! $this instanceof App) {
        return;
    }

    // middleware rest
    $this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
        /**
         * @var Hook $hook
         */
        $hook = $this['hook'];
        // add Hook For Not Found
        $hook->add('container.notFoundHandler', function () {
            /**
             * Invoke Hook with anonymous class extends to @uses NotFound
             * instanceof @uses AbstractHandler is important to hook not found
             * @hook container.notFoundHandler
             */
            return new class() extends NotFound {
                /**
                 * @param ServerRequestInterface $request
                 * @param ResponseInterface $response
                 *
                 * @return ResponseInterface
                 */
                public function __invoke(
                    ServerRequestInterface $request,
                    ResponseInterface $response
                ) : ResponseInterface {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(404),
                        new Exception(
                            "Target API endpoint is invalid."
                        )
                    );
                }
            };
        });

        // add Hook For Not Allowed
        $hook->add('container.notAllowedHandler', function () {
            /**
             * Invoke Hook with anonymous class extends to @uses NotAllowed
             * instanceof @uses AbstractHandler is important to hook not found
             * @hook container.notAllowedHandler
             */
            return new class() extends NotAllowed {
                /**
                 * @param ServerRequestInterface $request
                 * @param ResponseInterface $response
                 * @param array $methods
                 *
                 * @return ResponseInterface
                 */
                public function __invoke(
                    ServerRequestInterface $request,
                    ResponseInterface $response,
                    array $methods
                ) : ResponseInterface {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(405),
                        new Exception(
                            "Method not allowed on current target API."
                        )
                    );
                }
            };
        });

        // add Hook For PhpError
        $hook->add('container.phpErrorHandler', function () {
            /**
             * Invoke Hook with anonymous class extends to @uses PhpError
             * instanceof @uses AbstractError is important to hook not found
             * @hook container.phpErrorHandler
             */
            return new class() extends PhpError {
                /**
                 * @param ServerRequestInterface $request
                 * @param ResponseInterface $response
                 * @param Throwable $error
                 *
                 * @return ResponseInterface
                 */
                public function __invoke(
                    ServerRequestInterface $request,
                    ResponseInterface $response,
                    Throwable $error
                ) : ResponseInterface {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(500),
                        $error
                    );
                }
            };
        });

        /**
         * Returning next execution
         * @see MiddlewareAwareTrait::addMiddleware
         * on line 73 with :
         * --> $result = call_user_func($callable, $request, $response, $next);
         * Returning result must be as @return ResponseInterface
         */
        return $next($request, $response);
    });
}

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

    use Illuminate\Database\Capsule\Manager;
    use PentagonalProject\App\Rest\Record\AppFacade;
    use PentagonalProject\App\Rest\Record\ModularCollection;
    use PentagonalProject\App\Rest\Util\Hook;
    use PentagonalProject\Model\CookieSession;
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Slim\Http\Cookies;

    if (!isset($this) || ! $this instanceof App) {
        return;
    }

    // register Middleware

    /**
     * Middle ware to register Container
     */
    $this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
        if (isset($this['cookie'])) {
            unset($this['cookie']);
        }

        /**
         * @return CookieSession
         */
        $this['cookie'] = function () use ($request) : CookieSession {
            $cookies = new Cookies($request->getCookieParams());
            $cookieSession = new CookieSession($cookies);
            return $cookieSession;
        };

        /**
         * Add End Cookie for response
         * @var Hook[] $this
         */
        $this['hook']->add('response.end', function (ResponseInterface $response, ContainerInterface $container) {
            /**
             * @var CookieSession[] $container
             */
            return $response->withAddedHeader('Set-Cookie', $container['cookie']->toHeaders());
        });

        return $next($request, $response);
    });

    /**
     * Middle ware to register Module persistent
     */
    $this->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
        /**
         * @var Manager $capsule
         */
        $capsule = $this->database;
        // set default Connection
        $capsule->getDatabaseManager()->setDefaultConnection(AppFacade::current()->getName());

        /**
         * @var ModularCollection $Modular
         */
        $Modular = $this['module'];
        /**
         * @var string[] list Module To Load
         */
        $listModuleLoads = [
            'recipicious',
        ];

        // doing load
        array_map(function ($moduleName) use ($Modular) {
            $Modular->exist($moduleName) && $Modular->load($moduleName);
        }, $listModuleLoads);

        return $next($request, $response);
    });
}

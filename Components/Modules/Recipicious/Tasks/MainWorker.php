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

namespace PentagonalProject\Modules\Recipicious\Task;

use PentagonalProject\Modules\Recipicious\Lib\Api;
use PentagonalProject\Modules\Recipicious\Recipicious;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Class MainWorker
 * @package PentagonalProject\Modules\Recipicious\Tasks
 */
class MainWorker
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var bool
     */
    protected $hasRun = false;

    /**
     * @var string
     */
    protected $currentApp;

    /**
     * MainWorker constructor.
     *
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return Api
     */
    public function getApi() : Api
    {
        return $this->api;
    }

    /**
     * @param string $appName
     * @return MainWorker
     * @see MainWorker::sendRoutes() to get Routes collection set
     * @todo Just Info
     */
    public function run(string $appName)
    {
        if ($this->hasRun) {
            return $this;
        }

        $this->currentApp = $appName;
        $this->hasRun = true;

        return $this->sendRoutes();
    }

    /**
     * Build Routes REST FULL API
     */
    private function buildRoutesRestApi()
    {
        $route = RecipeRoute::class;
        # example use instance object
        $this
            ->api
            ->get(
                # pattern no group again to prevent inject to other group
                '[/]',
                # call getIndex
                [new RecipeRoute, "getIndex"],
                /**
                 * Ass callback
                 * 2 pass parameter RouteInterface -> (Route)
                 *              and API
                 */
                function (RouteInterface $route, Api $api) {
                    // no need to return
                }
            )
            /**
             * MiddleWare after group success
             */
            ->add(function (RequestInterface $request, ResponseInterface $response, $next) {
                // return next(RequestInterface, ResponseInterface);
                return $next($request, $response);
            });

        $this->api->post('[/]', "{$route}:postIndex");
        /**
         * example as string and separator is ::
         * {id: [1-9](?:[0-9]+)?} -> determine as a regex numeric only
         * 1. Must be start with 1-9 and end toh numeric 0-9 exist or no (optionals)
         */
        $this->api->get('/{id: [1-9](?:[0-9]+)?}[/]', "{$route}::getRecipeById");
        $this->api->post('/{id: [1-9](?:[0-9]+)?}[/]', "{$route}::postRecipeById");
        $this->api->delete('/{id: [1-9](?:[0-9]+)?}[/]', "{$route}::deleteRecipeById");
    }

    /**
     * @return MainWorker
     */
    private function sendRoutes() : MainWorker
    {
        // only send routes for REST FULL API ONLY
        if ($this->currentApp ==  Recipicious::REST_APP_NAME) {
            // build rest route
            $this->buildRoutesRestApi();
        }

        return $this;
    }
}

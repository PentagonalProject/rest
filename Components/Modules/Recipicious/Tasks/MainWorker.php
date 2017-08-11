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

use Apatis\ArrayStorage\Collection;
use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Abstracts\ResponseGeneratorAbstract;
use PentagonalProject\App\Rest\Generator\Response\Json;
use PentagonalProject\App\Rest\Generator\Response\Xml;
use PentagonalProject\App\Rest\Generator\ResponseStandard;
use PentagonalProject\App\Rest\Record\ModularCollection;
use PentagonalProject\Modules\Recipicious\Lib\Api;
use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
use PentagonalProject\Modules\Recipicious\Recipicious;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * Class MainWorker
 * @package PentagonalProject\Modules\Recipicious\Tasks
 */
class MainWorker
{
    /**
     * @var Recipicious
     */
    protected $module;

    /**
     * @var bool
     */
    protected $hasRun = false;

    /**
     * MainWorker constructor.
     * @param Recipicious $module
     */
    public function __construct(Recipicious &$module)
    {
        $this->module = $module;
    }

    /**
     * @return MainWorker
     * @see MainWorker::sendRoutes() to get Routes collection set
     * @todo Just Info
     */
    public function run()
    {
        if ($this->hasRun) {
            return $this;
        }
        $this->hasRun = true;

        return $this->sendRoutes();
    }

    /**
     * @return MainWorker
     */
    private function sendRoutes()
    {
        /**
         * Instance Slim as Container App
         * or uses ->
         *          AppFacade::current()->getAccessor()->getApp()
         *
         * @var App $slim
         */
//        $slim = $this->module->getContainer()['app'];
//        $class =& $this;
//        /*
//         * JUST ACCESS WITH:
//         * http://target/?output=xml -> to get XML data
//         */
//        $slim->any(
//            '{param: .+}',
//            function (ServerRequestInterface $request, ResponseInterface $response) use ($class) {
//                /**
//                 * @var ContainerInterface $this
//                 * @var ModularCollection $module
//                 */
//                $module = $this->module;
//
//                /* -------------------------------------------------------
//                 * INFO
//                 * ------------------------------------------------------ */
//                /**
//                 * Modular Collection Info
//                 */
//                $collection = $module->getModularInformation($class->module->getModularNameSelector());
//                /**
//                 * Get From @uses Collection
//                 */
//                $info = $collection->all();
//                // or use below to get module info
//                $info = $class->module->getModularInfo();
//
//                /* -------------------------------------------------------
//                 * BUILT IN JSON RESPONSE
//                 * ------------------------------------------------------ */
//                $responseBuilderClass = Json::class;
//                // if get param output == xml
//                if (isset($_GET['output']) && $_GET['output'] == 'xml') {
//                    // override ResponseGeneratorAbstract to Xml instance
//                    $responseBuilderClass = Xml::class;
//                }
//
//                /**
//                 * @uses Json|XML to generate Data JSON and Server it
//                 * @var ResponseGeneratorAbstract $responseBuilderClass
//                 */
//                $responseBuilder = $responseBuilderClass::generate($request, $response);
//                /**
//                 * set Data into @uses ResponseGeneratorAbstract
//                 */
//                $responseBuilder->setData(["Module" => $info]);
//                /**
//                 * Serve / Build The Response
//                 * @var ResponseInterface $response
//                 */
//                $response = $responseBuilder->serve();
//                return $response;
//            }
//        );

        /**
         * @var Api $api
         */
        $api = $this->module->getApi();

        $api->get(
            '/recipes',
            '',
            function (ServerRequestInterface $request, ResponseInterface $response) {
                /**
                 * Make request params fetchable.
                 *
                 * @var CollectionFetch $requestParams
                 */
                $requestParams = new CollectionFetch((array) $request->getQueryParams());

                return ResponseStandard::withData(
                    $request,
                    $response,
                    Recipe::filterByPage(
                        Recipe::query()->where('user_id', '!=', 'null'),
                        is_null($requestParams['page']) ? 1 : $requestParams['page']
                    )
                );
            }
        );

        $api->post(
            '/recipes',
            '',
            function (ServerRequestInterface $request, ResponseInterface $response) {
                /**
                 * Make request body fetchable.
                 *
                 * @var CollectionFetch $requestBody
                 */
                $requestBody = new CollectionFetch((array) $request->getParsedBody());

                try {
                    $check = [
                        'name',
                        'instructions',
                        'user_id'
                    ];

                    foreach ($check as $toCheck) {
                        // Check whether data is string
                        if (!is_string($requestBody[$toCheck])) {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    "Recipe %s should be as a string, %s given.",
                                    ucwords(str_replace('_', ' ', $toCheck)),
                                    gettype($requestBody[$toCheck])
                                ),
                                E_USER_WARNING
                            );
                        }

                        // Check whether data is not empty
                        if (trim($requestBody[$toCheck]) == '') {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    "Recipe %s should not be empty.",
                                    ucwords(str_replace('_', ' ', $toCheck))
                                ),
                                E_USER_WARNING
                            );
                        }

                        // Check name
                        if ($toCheck === 'name') {
                            // Check whether recipe name is not more than 60 characters
                            if (strlen($requestBody['name']) > 60) {
                                throw new \LengthException(
                                    "Recipe Name should not more than 60 characters.",
                                    E_USER_WARNING
                                );
                            }
                        }
                    }

                    // Instantiate recipe
                    $recipe = new Recipe([
                        'name'         => $requestBody['name'],
                        'instructions' => $requestBody['instructions'],
                        'user_id'      => $requestBody['user_id']
                    ]);

                    // Save or fail
                    $recipe->saveOrFail();

                    return ResponseStandard::withData(
                        $request,
                        $response->withStatus(201),
                        (int) $recipe->getKey()
                    );
                } catch (\Exception $exception) {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(406),
                        $exception
                    );
                }
            }
        );

        $api->get(
            '/recipes',
            '/{id}',
            function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
                try {
                    return ResponseStandard::withData(
                        $request,
                        $response,
                        Recipe::query()->findOrFail($params['id'])
                    );
                } catch (\Exception $exception) {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(404),
                        $exception
                    );
                }
            }
        );

        $api->post(
            '/recipes',
            '/{id}',
            function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
                /**
                 * Make request body fetchable.
                 *
                 * @var CollectionFetch $requestBody
                 */
                $requestBody = new CollectionFetch((array) $request->getParsedBody());

                try {
                    $check = [
                        'name',
                        'instructions',
                        'user_id'
                    ];

                    foreach ($check as $toCheck) {
                        // Check whether data is string
                        if (!is_string($requestBody[$toCheck])) {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    "Recipe %s should be as a string, %s given.",
                                    ucwords(str_replace('_', ' ', $toCheck)),
                                    gettype($requestBody[$toCheck])
                                ),
                                E_USER_WARNING
                            );
                        }

                        // Check whether data is not empty
                        if (trim($requestBody[$toCheck]) == '') {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    "Recipe %s should not be empty.",
                                    ucwords(str_replace('_', ' ', $toCheck))
                                ),
                                E_USER_WARNING
                            );
                        }

                        // Check name
                        if ($toCheck === 'name') {
                            // Check whether recipe name is not more than 60 characters
                            if (strlen($requestBody['name']) > 60) {
                                throw new \LengthException(
                                    "Recipe Name should not more than 60 characters.",
                                    E_USER_WARNING
                                );
                            }
                        }
                    }

                    // Get a recipe by id
                    $recipe = Recipe::query()->findOrFail($params['id']);

                    // Update found recipe
                    $recipe->update([
                        'name'         => $requestBody['name'],
                        'instructions' => $requestBody['instructions'],
                        'user_id'      => $requestBody['user_id']
                    ]);

                    return ResponseStandard::withData(
                        $request,
                        $response,
                        $recipe
                    );
                } catch (\Exception $exception) {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(406),
                        $exception
                    );
                }
            }
        );

        $api->delete(
            '/recipes',
            '/{id}',
            function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
                try {
                    // Get a recipe by id
                    $recipe = Recipe::query()->findOrFail($params['id']);

                    // Delete found recipe
                    $recipe->delete();

                    return ResponseStandard::withData(
                        $request,
                        $response,
                        'Recipe has been successfully deleted'
                    );
                } catch (\Exception $exception) {
                    return ResponseStandard::withException(
                        $request,
                        $response->withStatus(404),
                        $exception
                    );
                }
            }
        );

        return $this;
    }
}

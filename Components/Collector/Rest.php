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

    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\ModelNotFoundException;
    use PentagonalProject\App\Rest\Generator\Response\Json;
    use PentagonalProject\App\Rest\Util\ComposerLoaderPSR4;
    use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Slim\Http\Response;
    use Slim\Http\Uri;
    use Slim\Route;

    /**
     * @var App $this
     */
    ComposerLoaderPSR4::create([
        "PentagonalProject\\Model\\" => __DIR__ . "/../Models/",
    ])->register();

    // Require Common Middleware
    require_once __DIR__ . '/../Middlewares/CommonMiddleware.php';

    //$this->get(
    //    '/recipes[/[{page: [0-9]+}[/]]]',
    //    function (ServerRequestInterface $request, ResponseInterface $response, $params = []) {
    //        /**
    //         * @var Response $response
    //         * @var ContainerInterface $this
    //         * @var Route $route
    //         */
    //        $route = $request->getAttribute('route');
    //        $route->getArgument('arg1', 'default'); // << use route to get // Argument and get default set

    //        // get page param & get default
    //        if (($page = (int) $request->getAttribute('page', 1)) < 1) {
    //            return $response->withRedirect('/recipes');
    //        }

    //        return Json::generate($request, $response)
    //            ->setData(
    //                [
    //                    'status' => 200,
    //                    'response' => Recipe::filterByPage(
    //                        Recipe::where('user_id', '!=', 'null'),
    //                        $page
    //                    )
    //                ]
    //            )
    //            ->serve(true);
    //    }
    //)
    //    // set argument for route
    //    ->setName('recipe:get ')
    //    ->setArgument('arg1', 'arg1')
    //    ->setArguments([
    //        'arg2' => 'arg2'
    //    ]);

    $this->get(
        '/recipes',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            /**
             * Pagination
             *
             * @var string     $pageParam
             * @var string     $perPageParam
             * @var Collection $recipes
             * @var int        $totalRecipes
             * @var int        $page
             * @var int        $perPage
             * @var int        $offset
             * @var int        $firstPage
             * @var int        $lastPage
             * @var int        $previousPage
             * @var int        $nextPage
             */
            $pageParam = $request->getQueryParams()['page'];
            $perPageParam = $request->getQueryParams()['per_page'];
            $totalRecipes = Recipe::all()->count();
            $page = is_null($pageParam) ? 1 : (int) $pageParam;
            $perPage = is_null($perPageParam) ? 10 : (int) $perPageParam;
            $offset = ($page - 1) * $perPage;
            $firstPage = 1;
            $lastPage = ceil($totalRecipes / $perPage);
            $previousPage = $page <= $firstPage ? $firstPage : $page - 1;
            $nextPage = $page >= $lastPage ? $lastPage : $page + 1;

            /**
             * Item links
             *
             * @var Collection $recipesPerPage
             */
            $recipesPerPage = Recipe::query()->skip($offset)->take($perPage)->get();
            $recipesPerPage->transform(function ($item) use ($request) {
                return collect($item)->put('links', [
                    [
                        'rel' => 'self',
                        'href' => (string) $request->getUri()->withPath('recipes/' . $item->id)
                    ]
                ]);
            });

            /**
             * Pagination links
             *
             * @var Uri    $requestUri
             * @var string $firstPageUri
             * @var string $lastPageUri
             * @var string $previousPageUri
             * @var string $nextPageUri
             */
            $requestUri = $request->getUri();
            $firstPageUri = (string) $requestUri->withQuery('page=' . $firstPage . '&per_page=' . $perPage);
            $lastPageUri = (string) $requestUri->withQuery('page=' . $lastPage . '&per_page=' . $perPage);
            $previousPageUri = (string) $requestUri->withQuery('page=' . $previousPage . '&per_page=' . $perPage);
            $nextPageUri = (string) $requestUri->withQuery('page=' . $nextPage . '&per_page=' . $perPage);

            return Json::generate($request, $response)
                ->setData([
                    'code'   => $response->getStatusCode(),
                    'status' => 'success',
                    'data'   => $recipesPerPage,
                    'links'  => [
                        [
                            'rel'  => 'first-page',
                            'href' => $firstPageUri
                        ],
                        [
                            'rel'  => 'last-page',
                            'href' => $lastPageUri
                        ],
                        [
                            'rel'  => 'previous-page',
                            'href' => $previousPageUri
                        ],
                        [
                            'rel'  => 'next-page',
                            'href' => $nextPageUri
                        ]
                    ]
                ])
                ->serve(true);
        }
    );

    $this->post(
        '/recipes',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            try {
                /**
                 * Create a new recipe
                 *
                 * @var string $name
                 * @var string $instructions
                 * @var int    $userId
                 * @var Recipe $recipe
                 */
                $name = $request->getParsedBody()['name'];
                $instructions = $request->getParsedBody()['instructions'];
                $userId = (int) $request->getParsedBody()['user_id'];

                $recipe = new Recipe([
                    'name'         => $name,
                    'instructions' => $instructions,
                    'user_id'      => $userId
                ]);
                $recipe->saveOrFail();

                /**
                 * Modify response header
                 *
                 * @var string            $recipeId
                 * @var ResponseInterface $response
                 */
                $recipeId = (string) $recipe->getKey();
                $response = $response->withStatus(201);
                $response = $response->withHeader(
                    'Location',
                    (string) $request->getUri()->withPath('recipes/' . $recipeId)
                );

                return Json::generate($request, $response)
                    ->setData([
                        'code'   => $response->getStatusCode(),
                        'status' => 'success',
                        'data'   => $recipeId
                    ])
                    ->serve(true);
            } catch (Exception $exception) {
                $response = $response->withStatus(400);

                return Json::generate($request, $response)
                    ->setData([
                        'code'    => $response->getStatusCode(),
                        'status'  => 'error',
                        'message' => $exception->getMessage(),
                        'data'    => get_class($exception)
                    ])
                    ->serve(true);
            }
        }
    );

    $this->get(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                $recipe = Recipe::query()->findOrFail($params['id']);

                return Json::generate($request, $response)
                    ->setData([
                        'code'   => $response->getStatusCode(),
                        'status' => 'success',
                        'data'   => $recipe
                    ])
                    ->serve(true);
            } catch (Exception $exception) {
                $response = $response->withStatus(404);
                $exceptionName = substr(strrchr(get_class($exception), '\\'), 1);

                return Json::generate($request, $response)
                    ->setData([
                        'code'    => $response->getStatusCode(),
                        'status'  => 'error',
                        'message' => 'recipe not found',
                        'data'    => $exceptionName
                    ])
                    ->serve(true);
            }
        }
    );

    $this->post(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                /**
                 * Update a recipe
                 *
                 * @var string $name
                 * @var string $instructions
                 * @var Recipe $recipe
                 */
                $name = $request->getParsedBody()['name'];
                $instructions = $request->getParsedBody()['instructions'];
                $userId = $request->getParsedBody()['user_id'];

                $recipe = Recipe::query()->findOrFail($params['id']);
                $recipe->updateOrFail([
                    'name'         => $name,
                    'instructions' => $instructions,
                    'user_id'      => $userId
                ]);

                return Json::generate($request, $response)
                    ->setData([
                        'code'   => $response->getStatusCode(),
                        'status' => 'success',
                        'data'   => $recipe
                    ])
                    ->serve(true);
            } catch (ModelNotFoundException $exception) {
                $response = $response->withStatus(404);
                $exceptionName = substr(strrchr(get_class($exception), '\\'), 1);

                return Json::generate($request, $response)
                    ->setData([
                        'code'    => $response->getStatusCode(),
                        'status'  => 'error',
                        'message' => 'recipe not found',
                        'data'    => $exceptionName
                    ])
                    ->serve(true);
            } catch (Exception $exception) {
                $response = $response->withStatus(400);

                return Json::generate($request, $response)
                    ->setData([
                        'code'    => $response->getStatusCode(),
                        'status'  => 'error',
                        'message' => $exception->getMessage(),
                        'data'    => get_class($exception)
                    ])
                    ->serve(true);
            }
        }
    );

    $this->delete(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                $recipe = Recipe::query()->findOrFail($params['id']);
                $recipe->delete();

                return Json::generate($request, $response)
                    ->setData([
                        'code'   => $response->getStatusCode(),
                        'status' => 'success'
                    ])
                    ->serve(true);
            } catch (Exception $exception) {
                $response = $response->withStatus(404);
                $exceptionName = substr(strrchr(get_class($exception), '\\'), 1);

                return Json::generate($request, $response)
                    ->setData([
                        'code'    => $response->getStatusCode(),
                        'status'  => 'error',
                        'message' => 'recipe not found',
                        'data'    => $exceptionName
                    ])
                    ->serve(true);
            }
        }
    );
}

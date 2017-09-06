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

use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PentagonalProject\App\Rest\Generator\AccessToken;
use PentagonalProject\App\Rest\Generator\ResponseStandard;
use PentagonalProject\Model\Validator\AccessValidator;
use PentagonalProject\Model\Validator\EditorialStatus;
use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
use PentagonalProject\Modules\Recipicious\Model\Validator\RecipeValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RecipeRoute
 * @package PentagonalProject\Modules\Recipicious\Task
 *
 * Base On Recipe Route Collections
 * add change to static functions
 */
class RecipeRoute
{
    const LEVEL_GET    = 0;
    const LEVEL_UPDATE = 1;
    const LEVEL_DELETE = 2;
    const LEVEL_CREATE = 3;

    /**
     * RecipeRoute constructor.
     */
    public function __construct()
    {
        // before init route
    }

    /**
     * @param ServerRequestInterface $request
     * @param int $level
     * @throws UnauthorizedException
     */
    public static function validateAuth(ServerRequestInterface $request, int $level)
    {
        // Authenticate request and validate access
        AccessValidator::check(
            (int) AccessToken::fromRequest($request)->decryptData(),
            $level
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function getIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        try {
            // Authenticate request and validate access
            self::validateAuth($request, self::LEVEL_GET);

            /**
             * Make request params fetch able.
             *
             * @var CollectionFetch $requestParams
             */
            $requestParams = new CollectionFetch($request->getQueryParams());

            return ResponseStandard::withData(
                $request,
                $response,
                Recipe::filterByPage(
                    Recipe::where([
                        [Recipe::COLUMN_RECIPE_USER_ID, '!=', null],
                        [Recipe::COLUMN_RECIPE_STATUS, '=', EditorialStatus::PUBLISHED],
                    ]),
                    is_null($requestParams['page']) ? 1 : (int)$requestParams['page']
                )
            );
        } catch (UnauthorizedException $exception) {
            // unauthorized
            return ResponseStandard::withException(
                $request,
                $response->withStatus(401),
                $exception
            );
        } catch (\Exception $exception) {
            // error exception
            return ResponseStandard::withException(
                $request,
                $response->withStatus(500),
                $exception
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public static function postIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        try {
            // Authenticate request and validate access
            self::validateAuth($request, self::LEVEL_CREATE);

            /**
             * Make request body fetch able.
             *
             * @var CollectionFetch $requestBody
             */
            $requestBody = new CollectionFetch($request->getParsedBody());

            /**
             * @todo input $requestBody->all() is not always string[]
             * will be thrown notice on : line -> 163
             * just try to post with: tile[]=title&title[]=title2
             */
            // Trim every inputs
            $requestBody->replace(
                array_map(
                    function ($value) {
                        return trim($value);
                    },
                    $requestBody->all()
                )
            );

            // Validate request body
            RecipeValidator::check($requestBody);

            // Instantiate recipe
            $recipe = new Recipe([
                'title'        => $requestBody['title'],
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
        } catch (UnauthorizedException $exception) {
            // unauthorized
            return ResponseStandard::withException(
                $request,
                $response->withStatus(401),
                $exception
            );
        } catch (\Exception $exception) {
            // error exception
            return ResponseStandard::withException(
                $request,
                $response->withStatus(406),
                $exception
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $params
     *
     * @return ResponseInterface
     */
    public static function getRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ): ResponseInterface {
        try {
            // Authenticate request and validate access
            self::validateAuth($request, self::LEVEL_GET);

            $data = Recipe::where([
                [Recipe::COLUMN_RECIPE_ID, '=', $params['id']],
                [Recipe::COLUMN_RECIPE_STATUS, '=', EditorialStatus::PUBLISHED]
            ])->first();
            if (!$data) {
                throw new \RuntimeException(
                    sprintf(
                        'Recipe with id %d has not found.',
                        $params['id']
                    ),
                    E_NOTICE
                );
            }

            return ResponseStandard::withData(
                $request,
                $response,
                $data
            );
        } catch (UnauthorizedException $exception) {
            // unauthorized
            return ResponseStandard::withException(
                $request,
                $response->withStatus(401),
                $exception
            );
        } catch (\Exception $exception) {
            // error exception
            return ResponseStandard::withException(
                $request,
                $response->withStatus(404),
                $exception
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $params
     *
     * @return ResponseInterface
     */
    public static function postRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ) : ResponseInterface {
        try {
            // Authenticate request and validate access
            self::validateAuth($request, self::LEVEL_UPDATE);

            /**
             * Make request body fetch able.
             *
             * @var CollectionFetch $requestBody
             */
            $requestBody = new CollectionFetch($request->getParsedBody());

            // Trim every inputs
            $requestBody->replace(
                array_map(
                    function ($value) {
                        return trim($value);
                    },
                    $requestBody->all()
                )
            );

            // Validate request body
            RecipeValidator::check($requestBody);

            // Get a recipe by id
            $recipe = Recipe::query()->findOrFail($params['id']);

            // Update found recipe
            $recipe->update([
                'title'        => $requestBody['title'],
                'instructions' => $requestBody['instructions'],
                'user_id'      => $requestBody['user_id']
            ]);

            return ResponseStandard::withData(
                $request,
                $response,
                $recipe
            );
        } catch (UnauthorizedException $exception) {
            // unauthorized
            return ResponseStandard::withException(
                $request,
                $response->withStatus(401),
                $exception
            );
        } catch (\Exception $exception) {
            // error exception not accepted
            return ResponseStandard::withException(
                $request,
                $response->withStatus(406),
                $exception
            );
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $params
     *
     * @return ResponseInterface
     */
    public static function deleteRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ) : ResponseInterface {
        try {
            // Authenticate request and validate access
            self::validateAuth($request, self::LEVEL_DELETE);

            // Get a recipe by id
            $recipe = Recipe::query()->findOrFail($params['id']);

            // Delete found recipe
            $recipe->delete();

            return ResponseStandard::withData(
                $request,
                $response,
                'Recipe has been successfully deleted'
            );
        } catch (UnauthorizedException $exception) {
            // unauthorized
            return ResponseStandard::withException(
                $request,
                $response->withStatus(401),
                $exception
            );
        } catch (\Exception $exception) {
            // error exception error delete
            return ResponseStandard::withException(
                $request,
                $response->withStatus(500),
                $exception
            );
        }
    }
}

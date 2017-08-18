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
 * @author Zahroul Ulum <zahroul.ulum@gmail.com>
 */

declare(strict_types=1);

namespace PentagonalProject\Modules\Recipicious\Task;

use Apatis\ArrayStorage\CollectionFetch;
use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PentagonalProject\App\Rest\Generator\ResponseStandard;
use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
use PentagonalProject\Modules\Recipicious\Model\Validator\RecipeValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RecipeRoute
 * @package PentagonalProject\Modules\Recipicious\Task
 *
 * Base On Recipe Route Collections
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
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function getIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        try {
            // validate
            $this->validateAccess($request, $response, self::LEVEL_GET);

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
                    Recipe::query()->where('user_id', '!=', null),
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
    public function postIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        try {
            // validate
            $this->validateAccess($request, $response, self::LEVEL_CREATE);

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
    public function getRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ): ResponseInterface {
        try {
            // validate
            $this->validateAccess($request, $response, self::LEVEL_GET);

            return ResponseStandard::withData(
                $request,
                $response,
                Recipe::query()->findOrFail($params['id'])
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
    public function postRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ) : ResponseInterface {
        try {
            // validate
            $this->validateAccess($request, $response, self::LEVEL_UPDATE);

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
                'name'         => $requestBody['name'],
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
    public function deleteRecipeById(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $params
    ) : ResponseInterface {
        try {
            // validate
            $this->validateAccess($request, $response, self::LEVEL_DELETE);

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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $level
     *
     * @throws UnauthorizedException
     */
    private function validateAccess(
        ServerRequestInterface $request,
        ResponseInterface $response,
        int $level
    ) {
        /*
        throw new UnauthorizedException(
            "Not enough access"
        );*/
        // do validation
    }
}

<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    // Get all recipes
    $this->get(
        '/recipes',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            // Put collection to FetchAble
            $requestParams = new CollectionFetch($request->getQueryParams());

            return ResponseStandard::withData(
                $request,
                $response,
                Recipe::filterByPage(
                    Recipe::query()->where('user_id', '!=', 'null'),
                    is_null($requestParams->get('page')) ?: $requestParams->get('page')
                )
            );
        }
    );

    // Save a recipe
    $this->post(
        '/recipes',
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
                        throw new InvalidArgumentException(
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
                        throw new InvalidArgumentException(
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
                            throw new LengthException(
                                "Recipe Name should not more than 60 characters.",
                                E_USER_WARNING
                            );
                        }
                    }
                }

                /**
                 * Create a new recipe
                 */
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
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(406),
                    $exception
                );
            }
        }
    );

    // Get a recipe
    $this->get(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                return ResponseStandard::withData(
                    $request,
                    $response,
                    Recipe::query()->findOrFail($params['id'])
                );
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(404),
                    $exception
                );
            }
        }
    );

    // Update a recipe
    $this->post(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                // Put collection to FetchAble
                $bodyParsed = new CollectionFetch($request->getParsedBody());

                // Validate recipe name
                $name = $bodyParsed->get('name');

                // Check whether recipe name is string
                if (!is_string($name)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Recipe Name should be as a string, %s given.",
                            gettype($name)
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether recipe name is not empty
                if (trim($name) == '') {
                    throw new InvalidArgumentException(
                        "Recipe Name should not be empty.",
                        E_USER_WARNING
                    );
                }

                // Check whether recipe name is not more than 60 characters
                if (strlen($name) > 60) {
                    throw new LengthException(
                        "Recipe Name should not more than 60 characters.",
                        E_USER_WARNING
                    );
                }

                // Validate recipe instructions
                $instructions = $bodyParsed->get('instructions');

                // Check whether recipe instructions is not empty
                if (trim($instructions) == '') {
                    throw new InvalidArgumentException(
                        "Recipe Instructions should not be empty",
                        E_USER_WARNING
                    );
                }

                // Validate recipe user id
                $userId = $bodyParsed->get('user_id');

                // Check whether recipe user id is not empty
                if (trim($userId) == '') {
                    throw new InvalidArgumentException(
                        "Recipe User Id should not be empty",
                        E_USER_WARNING
                    );
                }

                // Get a recipe by id
                $recipe = Recipe::query()->findOrFail($params['id']);

                // Update found recipe
                $recipe->update([
                    'name'         => $name,
                    'instructions' => $instructions,
                    'user_id'      => $userId
                ]);

                return ResponseStandard::withData(
                    $request,
                    $response,
                    $recipe
                );
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(406),
                    $exception
                );
            }
        }
    );

    // Delete a recipe
    $this->delete(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                // Get a recipe by id
                $recipe = Recipe::query()->findOrFail($params['id']);

                // Delete found recipe
                $recipe->delete();

                return ResponseStandard::withData(
                    $request,
                    $response,
                    $recipe
                );
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(404),
                    $exception
                );
            }
        }
    );
}

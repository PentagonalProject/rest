<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use PentagonalProject\App\Rest\Generator\Response\Json;
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

            return ResponseStandard::with(
                $request,
                $response,
                Recipe::filterByPage(
                    Recipe::query()->where('user_id', '!=', 'null'),
                    is_null($requestParams->get('page')) ?: $requestParams->get('page')
                )
            )->noTrace()->serve(true);
        }
    );

    // Save a recipe
    $this->post(
        '/recipes',
        function (ServerRequestInterface $request, ResponseInterface $response) {
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
                        "Recipe Instructions should not be empty.",
                        E_USER_WARNING
                    );
                }

                // Validate recipe user id
                $userId = $bodyParsed->get('user_id');

                // Check whether recipe instructions is not empty
                if (trim($userId) == '') {
                    throw new InvalidArgumentException(
                        "Recipe User Id should not be empty.",
                        E_USER_WARNING
                    );
                }

                /**
                 * Create a new recipe
                 */
                $recipe = new Recipe([
                    'name'         => $name,
                    'instructions' => $instructions,
                    'user_id'      => $userId
                ]);

                // Save or fail
                $recipe->saveOrFail();
                return ResponseStandard::with(
                    $request,
                    $response->withStatus(201),
                    (int) $recipe->getKey()
                )->noTrace()->serve(true);
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(406),
                    $exception,
                    Json::class,
                    true
                );
            }
        }
    );

    $this->get(
        '/recipes/{id}',
        function (ServerRequestInterface $request, ResponseInterface $response, array $params) {
            try {
                return ResponseStandard::withData(
                    $request,
                    $response->withStatus(200),
                    Recipe::query()->findOrFail($params['id'])
                );
            } catch (Exception $exception) {
                return ResponseStandard::withData(
                    $request,
                    $response->withStatus(404),
                    'Recipe Not Found'
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

                return ResponseStandard::with(
                    $request,
                    $response,
                    $recipe
                )->noTrace()->serve(true);
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(406),
                    $exception,
                    Json::class,
                    true
                );
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

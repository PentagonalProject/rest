<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\Model\Database\User;
    use PentagonalProject\Model\Validator\UserValidator;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    $this->post('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var CollectionFetch $requestBody that use ArrayAccess
         * that mean access data just need to pass by array bracket
         *
         * -> $requestBody[keyName]
         */
        $requestBody = new CollectionFetch($request->getParsedBody());

        try {
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
            $userValidator = UserValidator::check($requestBody);

            // Set email as case fixed
            $requestBody['email'] = $userValidator->getCaseFixedEmail();

            // Instantiate password hash
            $passwordHash = new PasswordHash();

            // Instantiate user
            $user = new User([
                'first_name'  => $requestBody['first_name'],
                'last_name'   => $requestBody['last_name'],
                'username'    => $requestBody['username'],
                'email'       => $requestBody['email'],
                'password'    => $passwordHash->hash(sha1($requestBody['password'])),
                /**
                 * @uses microtime() has enough to generate very unique data double float
                 */
                'private_key' => hash('sha512', microtime())
            ]);

            // Save or fail
            $user->saveOrFail();

            return ResponseStandard::withData(
                $request,
                $response->withStatus(201),
                $user->getKey()
            );
        } catch (Exception $exception) {
            return ResponseStandard::withException(
                $request,
                $response->withStatus(406),
                $exception
            );
        }
    });
}

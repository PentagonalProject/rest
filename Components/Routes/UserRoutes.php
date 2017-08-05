<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\App\Rest\Util\Domain\Verify;
    use PentagonalProject\Exceptions\ValueUsedException;
    use PentagonalProject\Model\Database\User;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    $this->post('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var CollectionFetch $requestBody that use ArrayAccess
         * that mean access data just need to pass by array bracket
         *
         * -> $requestBody[keyName]
         */
        $requestBody = new CollectionFetch((array) $request->getParsedBody());

        try {
            // Data to check
            $check = [
                'first_name' => [ 'length' => 64 ],
                'last_name'  => [ 'length' => 64 ],
                'username'   => [ 'length' => 64 ],
                'email'      => [ 'length' => 255 ],
                'password'   => [ 'length' => 60 ]
            ];

            $domain = new Verify();
            foreach ($check as $toCheck => $value) {
                // Check whether data is string
                // Even though exists or not it will be check
                if (!is_string($requestBody[$toCheck])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Invalid %s",
                            ucwords(str_replace('_', ' ', $toCheck))
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether data is not empty
                if (trim($requestBody[$toCheck]) == '') {
                    throw new InvalidArgumentException(
                        sprintf(
                            "%s should not be empty",
                            ucwords(str_replace('_', ' ', $toCheck))
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether data length is not more than predetermined
                if (strlen($requestBody[$toCheck]) > $value['length']) {
                    throw new LengthException(
                        sprintf(
                            "%s should not more than %s characters",
                            ucwords(str_replace('_', ' ', $toCheck)),
                            $value['length']
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether username or email are already in use
                if ($toCheck === 'username' || $toCheck === 'email') {
                    if (User::query()->where($toCheck, $requestBody[$toCheck])->first()) {
                        throw new ValueUsedException(
                            sprintf(
                                "%s already in use",
                                ucwords($toCheck)
                            ),
                            E_USER_ERROR
                        );
                    }
                }

                // Check username
                if ($toCheck === 'username') {
                    // Check whether username is valid
                    if (!preg_match(
                        '/(?=^[a-z0-9_]{3,64}$)^[a-z0-9]+[_]?(?:[a-z0-9]+[_]?)?[a-z0-9]+$/',
                        $requestBody['username']
                    )
                    ) {
                        throw new InvalidArgumentException(
                            "Invalid username",
                            E_USER_ERROR
                        );
                    }
                }

                // Check email
                if ($toCheck === 'email') {
                    // Validate email with real valid email address
                    $email = $domain->validateEmail(
                        (string) $requestBody['email']
                    );

                    // Check whether email is valid
                    if (!$email) {
                        throw new InvalidArgumentException(
                            "Invalid email address",
                            E_USER_ERROR
                        );
                    }

                    // Set fix email
                    $requestBody['email'] = $email;
                    continue;
                }
            }

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

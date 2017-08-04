<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Abstracts\ResponseGeneratorAbstract;
    use PentagonalProject\App\Rest\Generator\Response\Json;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\App\Rest\Util\Domain\Verify;
    use PentagonalProject\Model\Database\User;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    $this->post('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
        /**
         * @var CollectionFetch $collectionFetch that use ArrayAccess
         * that mean access data just need to pass by array bracket
         *
         * -> $collectionFetch[keyName]
         */
        $collectionFetch = new CollectionFetch((array) $request->getParsedBody());

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
            foreach ($check as $key => $value) {
                // Check whether data is string
                // Even though exists or not it will be check
                if (!is_string($collectionFetch[$key])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Invalid %s",
                            ucwords(str_replace('_', ' ', $key))
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether data is not empty
                if (trim($collectionFetch[$key]) == '') {
                    throw new InvalidArgumentException(
                        sprintf(
                            "%s should not be empty",
                            ucwords(str_replace('_', ' ', $key))
                        ),
                        E_USER_WARNING
                    );
                }

                // Check whether data length is not more than predetermined
                if (strlen($collectionFetch[$key]) > $value['length']) {
                    throw new LengthException(
                        sprintf(
                            "%s should not more than %s characters",
                            ucwords(str_replace('_', ' ', $key)),
                            $value['length']
                        ),
                        E_USER_WARNING
                    );
                }

                // Check email
                if ($key === 'email') {
                    // Validate email with real valid email address
                    $email = $domain->validateEmail(
                        (string) $collectionFetch['email']
                    );

                    // Check whether email is valid
                    if (!$email) {
                        throw new InvalidArgumentException(
                            "Invalid email address",
                            E_USER_ERROR
                        );
                    }

                    // Set fix email
                    $collectionFetch->set('email', $email);
                    continue;
                }
            }

            // Instantiate password hash
            $passwordHash = new PasswordHash();

            // Instantiate user
            $user = new User([
                'first_name'  => $collectionFetch['first_name'],
                'last_name'   => $collectionFetch['last_name'],
                'username'    => $collectionFetch['username'],
                'email'       => $collectionFetch['email'],
                'password'    => $passwordHash->hash(sha1($collectionFetch['password'])),
                /**
                 * @uses microtime() has enough to generate very unique data double float
                 */
                'private_key' => hash('sha512', microtime())
            ]);

            // Save or fail
            $user->saveOrFail();

            return ResponseStandard::withData(
                $request,
                // response & data can pass here to prevent more memory usage on variable
                $response->withStatus(201),
                $user->getKey(),
                Json::class,
                /**
                 * magic additional arguments for @uses ResponseGeneratorAbstract::serve()
                 */
                true
            );
        } catch (Exception $exception) {
            return ResponseStandard::withData(
                $request,
                $response->withStatus(406),
                $exception,
                Json::class, // or just put null as value
                true // add pretty print on Json Generator
            );
        }
    });
}

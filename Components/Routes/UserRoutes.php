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
         * that mean acess data just need to pass by array bracket
         *
         * -> $collectionFetch[keyName]
         */
        $collectionFetch = new CollectionFetch((array) $request->getParsedBody());
        try {
            // data to check
            $check = [
                'first_name',
                'last_name',
                'username',
                'email',
                'password'
            ];

            $domain = new Verify();
            foreach ($check as $toCheck) {
                // do check email
                if ($toCheck === 'email') {
                    // doing validate email with real valid email address
                    $email = $domain->validateEmail(
                        (string) $collectionFetch['email']
                    );
                    if (!$email) {
                        throw new InvalidArgumentException(
                            "Invalid email address",
                            E_USER_ERROR
                        );
                    }
                    // set fix email
                    $collectionFetch->set('email', $email);
                    continue;
                }

                // validate data that must be string
                // even though exists or not it will be check
                if (!is_string($collectionFetch[$toCheck])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "Invalid %s",
                            ucwords(str_replace('_', ' ', $toCheck))
                        )
                    );
                }

                //  doing next validation
                // ..................
            }

            // Instantiate password hash
            $passwordHash = new PasswordHash();
            $user = new User([
                'first_name' => $collectionFetch['first_name'],
                'last_name' => $collectionFetch['last_name'],
                'username' => $collectionFetch['username'],
                'email' => $collectionFetch['email'],
                'password' => $passwordHash->hash(sha1($collectionFetch['password'])),
                /**
                 * @uses microtime() has enough to generate very unique data double float
                 */
                'private_key' => hash('sha512', microtime())
            ]);

            $user->saveOrFail();

            /**
             * Modify response header
             *
             * @var string $userId
             * @var ResponseInterface $response
             */
            $response = $response->withHeader(
                'Location',
                (string)$request->getUri()->withPath('users/' . $userId)
            );
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
        } catch (Exception $e) {
            return ResponseStandard::withData(
                $request,
                $response->withStatus(500),
                $e,
                Json::class, // or just put null as value
                true // add pretty print on Json Generator
            );
        }
    });
}

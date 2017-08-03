<?php
namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Generator\Response\Json;
    use PentagonalProject\Model\Database\User;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    $this->post('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
        $collectionFetch = new CollectionFetch((array) $request->getParsedBody());
        try {
            $check = [
                'first_name',
                'last_name',
                'username',
                'email',
                'password'
            ];
            foreach ($check as $toCheck) {
                if ($toCheck === 'email') {
                }
            }
            /**
             * Create a new user
             *
             * @var string $firstName
             * @var string $lastName
             * @var string $email
             * @var string password
             */
            $firstName = $request->getParsedBody()['first_name'];
            $lastName = $request->getParsedBody()['last_name'];
            $username = $request->getParsedBody()['username'];
            $email = $request->getParsedBody()['email'];
            $password = $request->getParsedBody()['password'];

            // Instantiate password hash
            $passwordHash = new PasswordHash();
            $user = new User([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => $email,
                'password' => $passwordHash->hash(sha1($password)),
                'private_key' => hash('sha512', microtime())
            ]);
            $user->saveOrFail();

            /**
             * Modify response header
             *
             * @var string $userId
             * @var ResponseInterface $response
             */
            $userId = $user->getKey();
            $response = $response->withStatus(201);
            $response = $response->withHeader(
                'Location',
                (string)$request->getUri()->withPath('users/' . $userId)
            );

            return Json::generate($request, $response)
                ->setData([
                    'code' => $response->getStatusCode(),
                    'status' => 'success',
                    'data' => $userId
                ])
                ->serve(true);
        } catch (Exception $e) {
        }
    });
}

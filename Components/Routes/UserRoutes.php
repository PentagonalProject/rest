<?php
namespace {

    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Generator\Response\Json;
    use PentagonalProject\Model\Database\User;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    $this->post('/users', function (ServerRequestInterface $request, ResponseInterface $response) {
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

        // Instantiate password hasher
        $passwordHasher = new PasswordHash();

        $user = new User([
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'username'    => $username,
            'email'       => $email,
            'password'    => $passwordHasher->hash(sha1($password)),
            'private_key' => bin2hex(random_bytes(64))
        ]);
        $user->saveOrFail();

        /**
         * Modify response header
         *
         * @var string            $userId
         * @var ResponseInterface $response
         */
        $userId = $user->getKey();
        $response = $response->withStatus(201);
        $response = $response->withHeader(
            'Location',
            (string) $request->getUri()->withPath('users/' . $userId)
        );

        return Json::generate($request, $response)
            ->setData([
                'code' => $response->getStatusCode(),
                'status' => 'success',
                'data' => $userId
            ])
            ->serve(true);
    });
}

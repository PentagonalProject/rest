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

namespace {

    use Apatis\ArrayStorage\CollectionFetch;
    use Pentagonal\PhPass\PasswordHash;
    use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
    use PentagonalProject\App\Rest\Generator\AccessToken;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\Model\Database\User;
    use PentagonalProject\Model\Database\UserMeta;
    use PentagonalProject\Model\Handler\UserAuthenticator;
    use PentagonalProject\Model\Validator\UserValidator;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;

    if (!isset($this) || ! $this instanceof App) {
        return;
    }

    $this->post(
        '/authenticate',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            try {
                $requestBody = new CollectionFetch($request->getParsedBody());

                return ResponseStandard::withData(
                    $request,
                    $response,
                    [
                        'access_token' => (string) AccessToken::fromData(
                            (string) UserAuthenticator::confirm(
                                $requestBody['username'],
                                $requestBody['password']
                            )
                        )
                    ]
                );
            } catch (UnauthorizedException $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response->withStatus(401),
                    $exception
                );
            } catch (Exception $exception) {
                return ResponseStandard::withException(
                    $request,
                    $response,
                    $exception
                );
            }
        }
    );

    $this->post(
        '/users',
        function (ServerRequestInterface $request, ResponseInterface $response) {
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

                // Create user meta for successfully saved user
                if ($user) {
                    UserMeta::create([
                        'user_id'    => $user->getKey(),
                        'meta_name'  => 'api_access',
                        'meta_value' => serialize([0, 1, 2, 3])
                    ]);
                }

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
        }
    );
}

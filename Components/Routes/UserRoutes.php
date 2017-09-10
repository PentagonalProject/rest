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
    use PentagonalProject\Model\Handler\Role;
    use PentagonalProject\Model\Handler\UserRole;
    use PentagonalProject\Model\Validator\AccessToken;
    use PentagonalProject\App\Rest\Generator\ResponseStandard;
    use PentagonalProject\Model\Database\User;
    use PentagonalProject\Model\Validator\AccessValidator;
    use PentagonalProject\Model\Validator\CommonHeaderValidator;
    use PentagonalProject\Model\Validator\UserValidator;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Symfony\Component\Translation\Translator;

    if (!isset($this) || ! $this instanceof App) {
        return;
    }

    $this->post(
        '/authenticate',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            try {
                $requestBody = new CollectionFetch((array) $request->getParsedBody());
                // generate common validator
                $newRequest = $request
                    // add username & password
                    ->withHeader(CommonHeaderValidator::AUTH_USER, $requestBody['username'])
                    ->withHeader(CommonHeaderValidator::AUTH_KEY, $requestBody['password'])
                    // remove access key & token
                    ->withoutHeader(CommonHeaderValidator::ACCESS_KEY)
                    ->withoutHeader(CommonHeaderValidator::ACCESS_TOKEN);

                $access = AccessToken::fromRequest($newRequest, false, false);
                /**
                 * @var Role $role
                 */
                $role = $this['role'];
                // create User Role
                $userRole = new UserRole($access->getUser(), $role);
                // check if user does not active
                if (!$userRole->isActive()) {
                    /**
                     * @var Translator[] $this
                     */
                    throw new UnauthorizedException(
                        $this['lang']->trans('Not enough access')
                    );
                }

                return ResponseStandard::withData(
                    $request,
                    $response,
                    [
                        'access_token' => $access->generateToken()
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
            $requestBody = new CollectionFetch((array) $request->getParsedBody());

            try {
                $newRequestBody = $requestBody->all();
                foreach ($newRequestBody as $key => $value) {
                    if (is_string($value)) {
                        $newRequestBody[$key] = trim($value);
                    }
                }

                // Trim every inputs
                $requestBody->replace($newRequestBody);

                // Validate request body
                $userValidator = UserValidator::check($requestBody);

                // Set email as case fixed
                $requestBody['email'] = $userValidator->getCaseFixedEmail();

                // Instantiate password hash
                $passwordHash = new PasswordHash();

                // Instantiate user
                $user = new User([
                    User::COLUMN_FIRST_NAME  => $requestBody['first_name'],
                    User::COLUMN_LAST_NAME   => $requestBody['last_name'],
                    User::COLUMN_USERNAME    => $requestBody['username'],
                    User::COLUMN_EMAIL       => $requestBody['email'],
                    User::COLUMN_PASSWORD    => $passwordHash->hash(sha1($requestBody['password'])),
                    /**
                     * @uses microtime() has enough to generate very unique data double float
                     */
                    User::COLUMN_PRIVATE_KEY => hash('sha512', microtime())
                ]);

                // Save or fail
                $user->saveOrFail();
                /**
                 * @var Role $role
                 */
                $role = $this['role'];
                // Create user meta for successfully saved user
                if ($user) {
                    // default for only read
                    $user->updateMetas([
                        UserRole::STATUS_META_SELECTOR => $role->getDefaultStatus(),
                        UserRole::ROLE_META_SELECTOR   => $role->getDefaultRole(),
                        'api_access' => [AccessValidator::LEVEL_GET]
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

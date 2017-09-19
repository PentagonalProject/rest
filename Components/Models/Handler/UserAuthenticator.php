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

namespace PentagonalProject\Model\Handler;

use Pentagonal\PhPass\PasswordHash;
use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PentagonalProject\App\Rest\Record\AppFacade;
use PentagonalProject\Model\Database\User;

/**
 * Class UserAuthenticator
 * @package PentagonalProject\Model\Handler
 */
final class UserAuthenticator
{
    const BY_USERNAME = User::COLUMN_USERNAME;
    const BY_EMAIL    = User::COLUMN_EMAIL;

    private $methodBy = self::BY_USERNAME;

    /**
     * @var null|string
     */
    private $authBase;

    /**
     * @var null|string
     */
    private $password;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * UserAuthenticator constructor.
     *
     * @param null|string $authBase
     * @param null|string $password
     * @param string $by
     */
    private function __construct($authBase, $password, $by = self::BY_USERNAME)
    {
        $this->authBase = $authBase;
        $this->password = $password;
        // by default change on by username
        $this->methodBy = $by === self::BY_EMAIL ? self::BY_EMAIL : self::BY_USERNAME;
    }

    /**
     * @throws UnauthorizedException
     */
    public static function throwUnauthorized()
    {
        throw new UnauthorizedException(AppFacade::access('lang')->trans('Not enough access'));
    }

    /**
     * Confirm the given username and password
     *
     * @param null|string $username
     * @param null|string $password
     * @param string      $by
     * @return UserAuthenticator
     */
    public static function confirm($username, $password, $by = self::BY_USERNAME) : UserAuthenticator
    {
        return (new static($username, $password, $by))->authenticate();
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->currentUser;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return (int) $this->getUser()[User::COLUMN_ID];
    }

    /**
     * @return string
     */
    public function getUseName() : string
    {
        return (string) $this->getUser()[User::COLUMN_USERNAME];
    }

    /**
     * @return int
     */
    public function getEmail() : int
    {
        return (int) $this->getUser()[User::COLUMN_EMAIL];
    }

    /**
     * Run the authenticator
     *
     * @return UserAuthenticator
     */
    private function authenticate() : UserAuthenticator
    {
        if (! is_string($this->password)
            || trim($this->password) == '' # never allowed password by whitespace only
            || ! is_string($this->authBase)
            || trim($this->authBase) == ''
        ) {
            $this->throwUnauthorized();
        }

        if (! is_string($this->authBase) || trim($this->authBase) == '') {
            $this->throwUnauthorized();
        }

        $this->currentUser = User::where($this->methodBy, $this->authBase)->first();
        if (! $this->currentUser instanceof User) {
            $this->throwUnauthorized();
        }

        $passwordHash = new PasswordHash();
        if (!$passwordHash->verify(sha1($this->password), $this->currentUser[User::COLUMN_PASSWORD])) {
            $this->throwUnauthorized();
        }

        return $this;
    }
}

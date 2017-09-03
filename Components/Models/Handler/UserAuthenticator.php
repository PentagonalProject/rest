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
use PentagonalProject\Model\Database\User;

/**
 * Class UserAuthenticator
 * @package PentagonalProject\Model\Handler
 */
class UserAuthenticator
{
    /**
     * @var null|string
     */
    private $username;

    /**
     * @var null|string
     */
    private $password;

    /**
     * UserAuthenticator constructor.
     *
     * @param null|string $username
     * @param null|string $password
     */
    private function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Confirm the given username and password
     *
     * @param null|string $username
     * @param null|string $password
     * @return int
     */
    public static function confirm($username, $password): int
    {
        $authenticator = new static($username, $password);

        return $authenticator->run();
    }

    /**
     * Check whether username is given
     *
     * @param null|string $username
     * @throws UnauthorizedException
     */
    private function isGiven($username)
    {
        if (is_null($username)) {
            throw new UnauthorizedException('Not enough access');
        }
    }

    /**
     * Check whether user is exist
     *
     * @param $user
     * @throws UnauthorizedException
     */
    private function isExist($user)
    {
        if (is_null($user)) {
            throw new UnauthorizedException('Not enough access');
        }
    }

    /**
     * Verify the password with the stored one.
     *
     * @param null|string $password
     * @param string      $storedPassword
     * @throws UnauthorizedException
     */
    private function verify($password, $storedPassword)
    {
        $passwordHash = new PasswordHash();

        if (!$passwordHash->verify(sha1($password), $storedPassword)) {
            throw new UnauthorizedException('Not enough access');
        }
    }

    /**
     * Run the authenticator
     *
     * @return int
     */
    private function run(): int
    {
        $this->isGiven($this->username);

        $user = User::where('username', $this->username)->first();

        $this->isExist($user);

        $this->verify($this->password, $user->password);

        return $user->id;
    }
}

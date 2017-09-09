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

namespace PentagonalProject\Model\Validator;

use Pentagonal\PhPass\PasswordHash;
use PentagonalProject\App\Rest\Util\GrantValidation;
use PentagonalProject\Model\Database\User;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;
use Slim\Interfaces\Http\HeadersInterface;

/**
 * Class CommonHeaderValidator
 * @package PentagonalProject\Model\Validator
 */
class CommonHeaderValidator
{
    const REASON_USER_EMPTY     = 1;
    const REASON_USER_INVALID   = 2;
    const REASON_TOKEN_EMPTY    = 3;
    const REASON_TOKEN_INVALID = 4;

    const AUTH_USER     = 'X-Auth-User';
    const AUTH_KEY      = 'X-Auth-Key';
    const ACCESS_KEY    = 'X-Access-Key';
    const ACCESS_TOKEN  = 'X-Access-Token';

    const HASH_LENGTH = 8;

    /**
     * @var array
     */
    protected $currentHeaders = [
        self::AUTH_USER    => null,
        self::AUTH_KEY     => null,
        self::ACCESS_TOKEN => null,
        self::ACCESS_KEY   => null,
    ];

    /**
     * @var bool
     */
    protected $withToken = true;
    protected $withAccessKey = true;

    /**
     * @var int
     */
    protected $reason;

    /**
     * @var GrantValidation
     */
    protected $grant;

    /**
     * @var HeadersInterface
     */
    protected $headers;

    /**
     * @var User
     */
    protected $user;

    /**
     * CommonHeaderValidator constructor.
     *
     * @param HeadersInterface $headers
     */
    public function __construct(HeadersInterface $headers)
    {
        $this->headers = $headers;
        $this->grant = new GrantValidation();
    }

    /**
     * @return CommonHeaderValidator
     */
    public function withoutToken() : CommonHeaderValidator
    {
        $this->withToken = false;
        return $this;
    }

    /**
     * @return CommonHeaderValidator
     */
    public function withToken() : CommonHeaderValidator
    {
        $this->withToken = true;
        return $this;
    }

    /**
     * @return CommonHeaderValidator
     */
    public function withoutAccessKey() : CommonHeaderValidator
    {
        $this->withAccessKey = false;
        return $this;
    }

    /**
     * @return CommonHeaderValidator
     */
    public function withAccessKey() : CommonHeaderValidator
    {
        $this->withAccessKey = true;
        return $this;
    }

    /**
     * @return GrantValidation
     */
    public function getGrant() : GrantValidation
    {
        $grant = GrantValidation::DENY;
        foreach ($this->headers as $key => $value) {
            $accessKey = ucwords($key, '-');
            $this->currentHeaders[$accessKey] = isset($value['value'][0]) ? $value['value'][0] : null;
        }

        $this->grant->setGrant($grant);
        $token    = $this->currentHeaders[self::ACCESS_TOKEN];
        if ($this->withToken && (
                !is_string($token)
                || trim($token) == ''
            )
        ) {
            $this->reason = self::REASON_TOKEN_EMPTY;
            return $this->grant;
        }

        $authUser = $this->currentHeaders[self::AUTH_USER];
        if (!is_string($authUser) || trim($authUser) == '') {
            $this->reason = self::REASON_USER_EMPTY;
            return $this->grant;
        }

        $this->user = User::where(User::COLUMN_USERNAME, $authUser)->first();
        if (empty($this->user) || ! $this->user instanceof User) {
            $this->reason = self::REASON_USER_INVALID;
            return $this->grant;
        }

        $authKey = $this->currentHeaders[self::AUTH_KEY];
        $passwordHash = new PasswordHash();
        $pasToken = is_string($authKey) && $authKey && $passwordHash->verify(sha1($authKey), $this->user[User::COLUMN_PASSWORD]);
        if (!$pasToken && $this->withToken) {
            // verify
            if ( !is_string($token) || ! $this->validateToken($token, $this->user)) {
                $this->reason = self::REASON_TOKEN_INVALID;
                return $this->grant;
            }

            $grant = GrantValidation::ONLY_READ;
        }

        $accessKey = $this->currentHeaders[self::ACCESS_KEY];

        if (!$this->withAccessKey) {
            $grant = GrantValidation::READ_WRITE;
        }

        if ($accessKey && $accessKey === $this->user[User::COLUMN_PRIVATE_KEY]) {
            if (!$pasToken) {
                $grant = GrantValidation::READ_WRITE;
            } else {
                $grant = GrantValidation::READ_WRITE_DELETE;
            }
        }

        $this->grant->setGrant($grant);
        return $this->grant;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return string
     */
    public static function generateTokenFromUser(User $user) : string
    {
        $passwordHash = new PasswordHash(self::HASH_LENGTH, false);
        $userName = strtolower($user[User::COLUMN_USERNAME]);
        $password = $user[User::COLUMN_PASSWORD];
        $password = $passwordHash->hash(sha1($userName . $password));
        return base64_encode(substr($password, 7));
    }

    /**
     * @return string
     */
    public function generateToken() : string
    {
        if (!$this->user instanceof User) {
            throw new \RuntimeException(
                'User data does not exists!'
            );
        }

        return self::generateTokenFromUser($this->user);
    }

    /**
     * @param string $token
     * @param User $user
     *
     * @return bool
     */
    public static function validateToken(string $token, User $user)
    {
        $encode = base64_decode($token);
        if (preg_match('/[^0-9a-z\.\/]/i', $encode)) {
            return false;
        }
        $length = self::HASH_LENGTH < 10 ? "0".self::HASH_LENGTH : self::HASH_LENGTH;
        $encode = "\$2a\${$length}\${$encode}";
        $passwordHash = new PasswordHash();
        $token = sha1(strtolower($user[User::COLUMN_USERNAME]) . $user[User::COLUMN_PASSWORD]);
        return $passwordHash->verify($token, $encode);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return CommonHeaderValidator
     */
    public static function fromRequest(ServerRequestInterface $request) : CommonHeaderValidator
    {
        return new static(new Headers($request->getHeaders()));
    }
}

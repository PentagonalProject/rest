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

namespace PentagonalProject\App\Rest\Generator;

use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AccessToken
 * @package PentagonalProject\App\Rest\Generator
 */
class AccessToken
{
    const REST_PUBLIC_KEY = 'b3Ny43g';

    /**
     * @var string
     */
    private $salt;

    /**
     * @var string
     */
    private $sign;

    /**
     * @var string
     */
    private $encryptedData;

    private function __construct()
    {
    }

    /**
     * Verify the sign
     *
     * @param string $sign
     * @throws UnauthorizedException
     */
    private function verifySign(string $sign)
    {
        if (! hash_equals($this->sign, $sign)) {
            throw new UnauthorizedException('Not enough access');
        }
    }

    /**
     * Create access token from given data
     *
     * @param string $data
     * @return static
     */
    public static function fromData(string $data)
    {
        $accessToken = new static();

        // Generate salt
        $accessToken->salt = random_bytes(16);

        // Generate secret key derived from Rest Public Key
        $secretKeys = hash_pbkdf2(
            'sha256',
            self::REST_PUBLIC_KEY,
            $accessToken->salt,
            80000,
            64,
            true
        );

        // Encrypt data
        $accessToken->encryptedData = openssl_encrypt(
            $data,
            'aes-256-cbc',
            mb_substr($secretKeys, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $accessToken->salt
        );

        // Generate sign
        $accessToken->sign = hash_hmac(
            'sha256',
            $accessToken->salt . $accessToken->encryptedData,
            mb_substr($secretKeys, 32, null, '8bit')
        );

        return $accessToken;
    }

    /**
     * Create access token from given request
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    public static function fromRequest(ServerRequestInterface $request)
    {
        $accessToken = new static();
        // Decode the request value
        $decodedValue = base64_decode(ltrim($request->getHeader('Authorization')[0], 'Bearer '));
        // Get the sign part
        $accessToken->sign = mb_substr($decodedValue, 0, 64, '8bit');
        // Get the salt part
        $accessToken->salt = mb_substr($decodedValue, 64, 16, '8bit');
        // Get the encrypted data part
        $accessToken->encryptedData = mb_substr($decodedValue, 80, null, '8bit');

        return $accessToken;
    }

    /**
     * Decrypt itself data
     *
     * @return string
     */
    public function decryptData()
    {
        // Generate latest secret key derived from Rest Public Key
        $latestSecretKeys = hash_pbkdf2(
            'sha256',
            self::REST_PUBLIC_KEY,
            $this->salt,
            80000,
            64,
            true
        );

        // Verify the latest sign
        $this->verifySign(hash_hmac(
            'sha256',
            $this->salt . $this->encryptedData,
            mb_substr($latestSecretKeys, 32, null, '8bit')
        ));

        // Decrypt data use latest encryption key
        return openssl_decrypt(
            $this->encryptedData,
            'aes-256-cbc',
            mb_substr($latestSecretKeys, 0, 32, '8bit'),
            OPENSSL_RAW_DATA,
            $this->salt
        );
    }

    public function __toString()
    {
        return base64_encode($this->sign . $this->salt . $this->encryptedData);
    }
}

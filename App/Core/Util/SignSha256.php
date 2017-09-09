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

namespace PentagonalProject\App\Rest\Util;

use RuntimeException;
use PentagonalProject\App\Rest\Abstracts\SignAbstract;

/**
 * Class SignSha256
 * @package PentagonalProject\App\Rest\Util
 */
class SignSha256 extends SignAbstract
{
    /**
     * @var mixed
     */
    protected $securityKey;

    /**
     * SignSha256 constructor.
     *
     * @param mixed $data
     * @param string $securityKey
     */
    public function __construct($data, $securityKey = null)
    {
        $this->securityKey = $this->convertKey($securityKey);
        parent::__construct($data);
    }

    /**
     * @param mixed $securityKey
     *
     * @return string
     */
    protected function convertKey($securityKey)
    {
        $securityKey = @serialize($securityKey);
        if (empty($securityKey)) {
            throw new RuntimeException(
                'Can not serialize security key.'
            );
        }

        return hash('sha256', $securityKey);
    }

    /**
     * @param mixed $data
     */
    protected function sign($data)
    {
        $this->signedString = $this->safeHash256($data);
    }

    /**
     * @param $data
     *
     * @return string sha256 data
     */
    protected function safeHash256($data) : string
    {
        /**
         * Make it Safe serialize
         */
        $data = @serialize($data);
        if (empty($data)) {
            throw new RuntimeException(
                'Can not sign data. Data can not serialized.'
            );
        }

        return hash_hmac('sha256', $data, $this->securityKey);
    }
}

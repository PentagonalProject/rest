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

namespace PentagonalProject\App\Rest\Abstracts;

use PentagonalProject\App\Rest\Interfaces\SignInterface;

/**
 * Class SignAbstract
 * @package PentagonalProject\App\Rest\Abstracts
 */
abstract class SignAbstract implements SignInterface
{
    /**
     * @var string
     */
    protected $signedString;

    /**
     * SignAbstract constructor.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->sign($data);
    }

    /**
     * @param mixed $data
     *
     * @return void
     */
    abstract protected function sign($data);

    /**
     * @return string
     */
    public function getSignedString() : string
    {
        return $this->signedString;
    }

    /**
     * @param mixed $data
     *
     * @return SignInterface
     */
    public static function fromData($data) : SignInterface
    {
        return new static($data);
    }
}

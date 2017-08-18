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

use Slim\Collection;

/**
 * Class ModelValidatorAbstract
 * @package PentagonalProject\App\Rest\Abstracts
 */
abstract class ModelValidatorAbstract
{
    /**
     * @var \ArrayAccess
     */
    protected $data;

    /**
     * ModelValidatorAbstract constructor.
     */
    public function __construct()
    {
        // set default
        $this->data = new Collection();
    }

    /**
     * @param \ArrayAccess $data
     *
     * @return static
     */
    abstract public static function check(\ArrayAccess $data);

    /**
     * Attributes to check
     *
     * @return array
     */
    abstract protected function toCheck() : array;

    /**
     * Run the validator
     */
    abstract protected function run();

    /**
     * Check whether data attribute value is string
     *
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    abstract protected function mustBeString(string $attribute);

    /**
     * Check whether data attribute value is not empty
     *
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    abstract protected function mustBeNotEmpty(string $attribute);

    /**
     * Check whether data attribute value length is not more than given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    abstract protected function lengthMustBeLessThan(
        string $attribute,
        int $length
    );

    /**
     * Check whether data attribute value length is more than given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    abstract protected function lengthMustBeMoreThan(
        string $attribute,
        int $length
    );

    /**
     * Check whether data attribute value length is between range than given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $min
     * @param int          $max
     *
     * @throws \LengthException
     */
    abstract protected function lengthMustBeBetween(
        string $attribute,
        int $min,
        int $max
    );

    /**
     * Check whether data attribute value length is equal than given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    abstract protected function lengthMustBeEqual(
        string $attribute,
        int $length
    );
}

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

abstract class ModelValidatorAbstract
{
    /**
     * @var \ArrayAccess
     */
    protected $data;

    protected function __construct()
    {
    }

    /**
     * Check the given data
     *
     * @param \ArrayAccess $data
     */
    public static function check(\ArrayAccess $data)
    {
        $modelValidator = new static();
        $modelValidator->data = $data;
        $modelValidator->run();

        return $modelValidator;
    }

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
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    protected function isString(\ArrayAccess $data, $attribute)
    {
        if (! is_string($data[$attribute])) {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should be as a string, %s given.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    gettype($data[$attribute])
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether data attribute value is not empty
     *
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    protected function isNotEmpty(\ArrayAccess $data, $attribute)
    {
        if (trim($data[$attribute]) == '') {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should not be empty.",
                    ucwords(str_replace('_', ' ', $attribute))
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether data attribute value length is not more than given
     * number of characters
     *
     * @param \ArrayAccess $data
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    protected function lengthIsNotMoreThan(
        \ArrayAccess $data,
        $attribute,
        $length
    ) {
        if (strlen($data[$attribute]) > $length) {
            throw new \LengthException(
                sprintf(
                    "%s length should not more than %s characters.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    $length
                ),
                E_USER_WARNING
            );
        }
    }
}

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

namespace PentagonalProject\App\Rest\Traits;

/**
 * Trait ModelValidatorTrait
 * @package PentagonalProject\App\Rest\Traits
 */
trait ModelValidatorTrait
{
    /**
     * @var \ArrayAccess
     */
    protected $data;

    /**
     * @param \ArrayAccess $data
     *
     * @return static
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
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    protected function mustBeString(string $attribute)
    {
        if (! is_string($this->data[$attribute])) {
            throw new \InvalidArgumentException(
                sprintf(
                    "%s should be as a string, %s given.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    gettype($this->data[$attribute])
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether data attribute value is not empty
     *
     * @param string       $attribute
     * @throws \InvalidArgumentException
     */
    protected function mustBeNotEmpty(string $attribute)
    {
        if ($this->determineLength($this->data[$attribute]) !== 0) {
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
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    protected function lengthMustBeLessThan(
        string $attribute,
        int $length
    ) {
        if ($this->determineLength($this->data[$attribute]) > $length) {
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

    /**
     * Check whether data attribute value length is more than given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    protected function lengthMustBeMoreThan(
        string $attribute,
        int $length
    ) {
        if ($this->determineLength($this->data[$attribute]) < $length) {
            throw new \LengthException(
                sprintf(
                    "%s length should more than %s characters.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    $length
                ),
                E_USER_WARNING
            );
        }
    }

    protected function determineLength($value)
    {
        if ($value instanceof \Countable || is_array($value)) {
            return count($value);
        }
        if ($value instanceof \Iterator) {
            $lg = 0;
            $value = clone $value;
            foreach ($value as $val) {
                $lg++;
            }
            return $lg;
        }
        if (!$value) {
            return 0;
        }
        if (is_string($value) || is_bool($value)) {
            return strlen((string) $value); # casting
        }

        throw new \TypeError(
            'Type value must be count able %s given',
            gettype($value)
        );
    }

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
    protected function lengthMustBeBetween(
        string $attribute,
        int $min,
        int $max
    ) {
        $length = $this->determineLength($this->data[$attribute]);
        if ($length < $min || $length > $max) {
            throw new \LengthException(
                sprintf(
                    "%s length should between range from %d and %d on.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    $min,
                    $max
                ),
                E_USER_WARNING
            );
        }
    }

    /**
     * Check whether data attribute value length is equal with given
     * number of characters
     *
     * @param string       $attribute
     * @param int          $length
     * @throws \LengthException
     */
    protected function lengthMustBeEqual(
        string $attribute,
        int $length
    ) {
        if ($this->determineLength($this->data[$attribute]) <> $length) {
            throw new \LengthException(
                sprintf(
                    "%s length should equal %s characters.",
                    ucwords(str_replace('_', ' ', $attribute)),
                    $length
                ),
                E_USER_WARNING
            );
        }
    }
}

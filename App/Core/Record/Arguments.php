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

namespace PentagonalProject\App\Rest\Record;

use ArrayAccess;
use Countable;

/**
 * Class Arguments
 * @package PentagonalProject\App\Rest\Record
 */
class Arguments implements Countable, ArrayAccess
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Arguments constructor.
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->arguments =& $arguments;
    }

    /**
     * Get Arguments
     *
     * @param int|string|float $offset
     * @param mixed $default
     * @return mixed
     */
    public function &get($offset, $default = null)
    {
        if ($this->has($offset)) {
            return $this->arguments[$offset];
        }

        return $default;
    }

    /**
     * Check if has Arguments
     *
     * @param int|mixed|float $offset
     * @return bool
     */
    public function has($offset) : bool
    {
        return (array_key_exists($offset, $this->arguments));
    }

    /**
     * Set Arguments
     *
     * @param int|string|float $offset
     * @param mixed $value
     */
    public function set($offset, $value)
    {
        $this->arguments[$offset] = $value;
    }

    /**
     * Remove/Unset Arguments
     *
     * @param int|string|float $offset
     */
    public function remove($offset)
    {
        unset($this->arguments[$offset]);
    }

    /**
     * Count Arguments
     *
     * @return int
     */
    public function count() : int
    {
        return count($this->arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {
        return $this->has($offset);
    }
}

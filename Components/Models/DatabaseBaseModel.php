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

namespace PentagonalProject\Model;

use Illuminate\Database\Eloquent\Model;
use PentagonalProject\App\Rest\Util\Sanitizer;

/**
 * Sanitize Per Execution Standard SQL as Serialized String if contains of non serialized
 *
 * Class DatabaseBaseModel
 * @package PentagonalProject\Model
 */
class DatabaseBaseModel extends Model
{
    /**
     * @param mixed $values
     * @return mixed
     */
    public function resolveResult($values)
    {
        return Sanitizer::maybeUnSerialize($values);
    }

    /**
     * @param mixed $values
     * @return mixed
     */
    public function resolveSet($values)
    {
        return Sanitizer::maybeSerialize($values);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->resolveResult(parent::offsetGet($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, $this->resolveSet($value));
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        parent::__set($key, $this->resolveResult($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($key)
    {
        return $this->resolveResult(parent::getAttribute($key));
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->resolveResult(parent::__get($key));
    }
}

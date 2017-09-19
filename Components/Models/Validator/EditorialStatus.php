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

namespace PentagonalProject\Model\Validator;

use PentagonalProject\App\Rest\Interfaces\TypeStatusInterface;

/**
 * Class EditorialStatus
 * @package PentagonalProject\Model\Validator
 */
class EditorialStatus implements TypeStatusInterface
{
    const DRAFT     = 1;
    const TRASH     = 2;
    const PUBLISHED = 3;

    /**
     * @var int
     */
    protected $currentStatus;

    /**
     * EditorialStatus constructor.
     *
     * @param int $status
     */
    public function __construct(int $status)
    {
        $this->currentStatus = $status;
    }

    /**
     * @param int $int
     */
    public function setStatus(int $int)
    {
        $this->currentStatus = $int;
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    public function is(int $type): bool
    {
        return $this->currentStatus === $type;
    }

    /**
     * @return bool
     */
    public function isPublished() : bool
    {
        return $this->is(self::PUBLISHED);
    }

    /**
     * @return bool
     */
    public function isDraft() : bool
    {
        return $this->is(self::DRAFT);
    }

    /**
     * @return bool
     */
    public function isTrash() : bool
    {
        return $this->is(self::TRASH);
    }
}

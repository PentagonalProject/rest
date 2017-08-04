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

namespace PentagonalProject\App\Rest\Interfaces;

/**
 * Interface GrantInterface
 * @package PentagonalProject\App\Rest\Interfaces
 */
interface GrantInterface
{
    // deny
    const DENY = -1;

    // read only
    const ONLY_READ  = 1;

    // Only Write
    const ONLY_WRITE = 2;

    // allow read write
    const READ_WRITE = 3;

    // only delete
    const ONLY_DELETE = 4;

    // only read & delete without write
    const ONLY_READ_DELETE = 5;

    // only write & delete no read
    const ONLY_WRITE_DELETE = 6;

    // read write & delete
    const READ_WRITE_DELETE = 7;

    /**
     * @param int $grant
     * @return bool
     */
    public function hasGrantedFor(int $grant) : bool;

    /**
     * @param int $grant
     * @return void
     */
    public function setGrant(int $grant);
}

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

use PentagonalProject\App\Rest\Interfaces\GrantInterface;

/**
 * Class GrantValidation
 * @package PentagonalProject\App\Rest\Util
 */
class GrantValidation implements GrantInterface
{
    /**
     * Grant Value that default use deny access
     * @uses GrantInterface::DENY
     *
     * @var int
     */
    protected $currentSetGranted = self::DENY;

    /**
     * @var int
     */
    protected $oldSetGranted;

    /**
     * @param int $grant
     */
    public function setGrant(int $grant)
    {
        // sanity
        if ($grant < self::ONLY_READ) {
            $grant = self::DENY;
        } elseif ($grant > self::READ_WRITE_DELETE) {
            $grant = self::READ_WRITE_DELETE;
        }

        $this->currentSetGranted = $grant;
    }

    /**
     * Is Granted to Access for
     * @todo calculation grant
     *
     * @param int $grant
     * @return bool
     */
    public function hasGrantedFor(int $grant): bool
    {
        // fix granted
        if ($this->currentSetGranted < self::ONLY_READ
            || $this->currentSetGranted > self::READ_WRITE_DELETE
        ) {
            // set old grant
            if ($this->currentSetGranted <> self::DENY) {
                $this->oldSetGranted = $this->currentSetGranted;
            }

            // normalize
            $this->currentSetGranted = $this->currentSetGranted < self::ONLY_READ
                ? self::DENY
                : self::READ_WRITE_DELETE;
        }

        $grant = $grant >= self::READ_WRITE_DELETE
            ? self::READ_WRITE_DELETE
            : ($grant < self::ONLY_READ ? self::DENY : $grant);

        // if has equal grant
        if ($grant === $this->currentSetGranted) {
            return true;
        }

        $granted = ($grant | $this->currentSetGranted);
        $granted = $granted === $this->currentSetGranted
            || ($grant | $this->currentSetGranted) <= $this->currentSetGranted;

        if ($grant === self::DENY) {
            return $granted < self::ONLY_READ;
        }

        return $granted;
    }

    /**
     * @return int
     */
    public function getCurrentSetGranted(): int
    {
        return $this->currentSetGranted;
    }

    /**
     * @return bool
     */
    public function isDeny() : bool
    {
        return $this->hasGrantedFor(self::DENY);
    }

    /**
     * @return bool
     */
    public function isAllowToWrite() : bool
    {
        return $this->hasGrantedFor(self::ONLY_WRITE);
    }

    /**
     * @return bool
     */
    public function isAllowToRead() : bool
    {
        return $this->hasGrantedFor(self::ONLY_READ);
    }

    /**
     * @return bool
     */
    public function isAllowToDelete() : bool
    {
        return $this->hasGrantedFor(self::ONLY_DELETE);
    }
}

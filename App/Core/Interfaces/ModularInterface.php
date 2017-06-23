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
 * Interface ModularInterface
 * @package PentagonalProject\App\Rest\Interfaces
 */
interface ModularInterface
{
    const NAME        = 'name';
    const VERSION     = 'version';
    const AUTHOR      = 'author';
    const AUTHOR_URI  = 'author_uri';
    const URI         = 'uri';
    const DESCRIPTION = 'description';
    const CLASS_NAME  = 'class_name';
    const FILE_PATH   = 'file_path';

    /**
     * Get Modular Info
     *
     * @return array
     */
    public function getModularInfo() : array;

    /**
     * Get Modular Name
     *
     * @return string
     */
    public function getModularName() : string;

    /**
     * Get Modular Version commonly string|integer|float
     *
     * @return string
     */
    public function getModularVersion() : string;

    /**
     * Get Modular Author
     *
     * @return string
     */
    public function getModularAuthor() : string;

    /**
     * Get Modular Author
     *
     * @return string
     */
    public function getModularAuthorUri() : string;

    /**
     * Get Modular URL
     *
     * @return string
     */
    public function getModularUri() : string;

    /**
     * Get Description of Modular
     *
     * @return string
     */
    public function getModularDescription() : string;

    /**
     * Initialize Modular
     *
     * @return mixed
     */
    public function init();
}

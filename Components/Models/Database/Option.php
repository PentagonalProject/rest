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

namespace PentagonalProject\Model\Database;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PentagonalProject\Model\DatabaseBaseModel;

/**
 * Class Option
 * @package PentagonalProject\Model\Database
 */
class Option extends DatabaseBaseModel
{
    /*-------------------------------------
     * TABLE NAME DEFINITION
     * ----------------------------------- */
    const TABLE_NAME = 'options';

    /*-------------------------------------
     * COLUMN DEFINITION
     * ----------------------------------- */
    const COLUMN_OPTION_ID    = 'id';
    const COLUMN_OPTION_NAME  = 'option_name';
    const COLUMN_OPTION_VALUE = 'option_value';

    /*-------------------------------------
     * OVERRIDE
     * ----------------------------------- */
    /**
     * Disable Time Stamp
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = self::COLUMN_OPTION_NAME;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getFrom(string $name, $default = null)
    {
        /**
         * @var Option $model
         */
        $model = self::find($name);
        if (! $model || ! $model instanceof Model) {
            return $default;
        }

        $model = new Collection($model->toArray());
        return $model->get(self::COLUMN_OPTION_VALUE, $default);
    }
}

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

namespace PentagonalProject\Modules\Recipicious\Model\Database;

use Illuminate\Database\Eloquent\Builder;
use PentagonalProject\Model\DatabaseBaseModel;
use stdClass;

/**
 * Class Recipe
 * @package PentagonalProject\Modules\Recipicious\Model\Database
 */
class Recipe extends DatabaseBaseModel
{
    const COLUMN_RECIPE_TITLE        = 'title';
    const COLUMN_RECIPE_SLUG         = 'slug';
    const COLUMN_RECIPE_INSTRUCTIONS = 'instructions';
    const COLUMN_RECIPE_USER_ID      = 'user_id';
    const COLUMN_RECIPE_STATUS       = 'status';
    const COLUMN_PUBLISHED_AT        = 'published_at';
    const COLUMN_CREATED_AT          = self::CREATED_AT;
    const COLUMN_UPDATED_AT          = self::UPDATED_AT;

    /**
     * {@inheritdoc}
     * @var array
     */
    protected $fillable = [
        self::COLUMN_RECIPE_TITLE,
        self::COLUMN_RECIPE_INSTRUCTIONS,
        self::COLUMN_RECIPE_USER_ID
    ];

    /**
     * @var string[] column that PostGreSQL Date TIMESTAMP possible
     * Y-m-d H:i:s.u
     */
    protected $checkDateFormat = [
        self::COLUMN_CREATED_AT,
        self::COLUMN_UPDATED_AT,
        self::COLUMN_PUBLISHED_AT
    ];

    /**
     * Example Limiting Page On Result
     *
     * @param Builder $builder
     * @param int $page
     * @param int $limit
     * @return stdClass
     */
    public static function filterByPage(Builder $builder, int $page = 1, int $limit = 10) : stdClass
    {
        $results              = new stdClass;
        $results->itemCount   = 0;
        $results->totalItems  = $builder->count();
        $results->currentPage = $page;
        $results->perPage     = $limit;
        $results->totalPage   = (int) ceil($results->totalItems / $limit);
        $results->items       = $builder->skip($limit * ($page - 1))->take($limit)->get()->all();
        $results->itemCount   = count($results->items);

        return $results;
    }
}

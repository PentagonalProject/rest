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
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'instructions',
        'user_id'
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
        $results = new stdClass;
        $results->itemCount  = 0;
        $results->totalItems   = $builder->count();
        $results->currentPage  = $page;
        $results->perPage      = $limit;
        $results->totalPage    = (int) ceil($results->totalItems / $limit);
        $results->items        = $builder->skip($limit * ($page - 1))->take($limit)->get()->all();
        $results->itemCount   = count($results->items);

        return $results;
    }

    /**
     * Validate the model filled attributes
     *
     * @return $this
     * @throws \UnexpectedValueException
     * @throws \LengthException
     */
    public function validateAttributes()
    {
        foreach ($this->attributes as $key => $value) {
            if (empty($value)) {
                throw new \UnexpectedValueException(ucfirst($key) . ' shouldn\'t be empty');
            } elseif ($key == 'name' && strlen($value) > 60) {
                throw new \LengthException(ucfirst($key) . ' shouldn\'t more than 60 characters');
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * Save the model
     */
    public function saveOrFail(array $options = [])
    {
        $this->validateAttributes();

        parent::saveOrFail();
    }
}

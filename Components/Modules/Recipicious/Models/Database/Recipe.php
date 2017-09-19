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
use Illuminate\Support\Carbon;
use PentagonalProject\App\Rest\Record\AppFacade;
use PentagonalProject\Model\DatabaseBaseModel;
use PentagonalProject\Model\Validator\EditorialStatus;
use stdClass;

/**
 * Class Recipe
 * @package PentagonalProject\Modules\Recipicious\Model\Database
 */
class Recipe extends DatabaseBaseModel
{
    const COLUMN_RECIPE_ID           = 'id';
    const COLUMN_RECIPE_TITLE        = 'title';
    const COLUMN_RECIPE_SLUG         = 'slug';
    const COLUMN_RECIPE_INSTRUCTIONS = 'instructions';
    const COLUMN_RECIPE_USER_ID      = 'user_id';
    const COLUMN_RECIPE_STATUS       = 'status';
    const COLUMN_PUBLISHED_AT        = 'published_at';
    const COLUMN_CREATED_AT          = self::CREATED_AT;
    const COLUMN_UPDATED_AT          = self::UPDATED_AT;

    /**
     * @var array
     */
    protected $noFixation = [
        self::COLUMN_RECIPE_ID,
        self::COLUMN_RECIPE_TITLE,
        self::COLUMN_RECIPE_SLUG,
        self::COLUMN_RECIPE_USER_ID,
        self::COLUMN_RECIPE_STATUS,
        self::COLUMN_CREATED_AT,
        self::COLUMN_UPDATED_AT,
        self::COLUMN_PUBLISHED_AT,
    ];

    /**
     * @var string
     */
    protected $primaryKey = self::COLUMN_RECIPE_ID;

    /**
     * {@inheritdoc}
     * @var array
     */
    protected $fillable = [
        self::COLUMN_RECIPE_TITLE,
        self::COLUMN_RECIPE_INSTRUCTIONS,
        self::COLUMN_RECIPE_SLUG,
        self::COLUMN_RECIPE_USER_ID,
        self::COLUMN_PUBLISHED_AT
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
     * @var bool hanlde to prevent multiple looping
     */
    private static $performCheckSlug = false;

    /**
     * @param array $attributes
     * @param bool $sync
     *
     * @return DatabaseBaseModel
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $data =  parent::setRawAttributes($attributes, $sync);
        if ($data[self::COLUMN_RECIPE_STATUS] == EditorialStatus::PUBLISHED
            && ! $data[self::COLUMN_PUBLISHED_AT]
        ) {
            $data->timestamps = false;
            $data[self::COLUMN_PUBLISHED_AT] = $data[self::UPDATED_AT];
            $data->save();
            $data->timestamps = true;
        }

        return $data;
    }

    /**
     * @param string $slug
     *
     * @return bool|mixed|string
     */
    public function checkUniqueSlug(string $slug)
    {
        if (self::$performCheckSlug) {
            return $slug;
        }
        self::$performCheckSlug = true;
        $sanity = preg_replace(
            [
                '/[^a-z0-9\_-]/',
                '/[\-]+/'
            ],
            '-',
            strtolower($slug)
        );
        $sanity = trim($sanity, '-_');
        $length = 160;
        $sanity = substr($sanity, 0, $length);
        if (strlen($sanity) === 0) {
            $sanity = sha1((string) microtime(true));
        }
        $newSanity = $sanity;
        if (isset($this->id)) {
            if ($this[self::COLUMN_RECIPE_SLUG] == $sanity) {
                self::$performCheckSlug = false;
                return $sanity;
            }

            if (!is_int(abs($this->id))) {
                throw new \LogicException(
                    sprintf(
                        AppFacade::access('lang')->trans('id must be as a integer %s given'),
                        gettype($this->id)
                    )
                );
            }

            $data = self::where([
                [self::COLUMN_RECIPE_SLUG, '=', $sanity],
                [self::COLUMN_RECIPE_ID, '<>' , $this->id]
            ])->first();
            if ($data) {
                $c = 0;
                if (preg_match('/(?P<slug>.+)\-(?P<inc>[0-9])+$/', $sanity, $match)
                    && ! empty($match['inc'])
                ) {
                    $sanity = $match['slug'];
                    $c      = $match['inc'];
                }
                while ($data) {
                    $c++;
                    $sanity    = substr($sanity, 0, $length - ($c + 1));
                    $newSanity = "{$sanity}-{$c}";
                    $data      = self::where([
                        [self::COLUMN_RECIPE_SLUG, '=', $newSanity],
                        [self::COLUMN_RECIPE_ID, '<>', $this->id]
                    ])->first();
                }
            }

            self::$performCheckSlug = false;
            return $newSanity;
        }

        $data = self::where(
            self::COLUMN_RECIPE_SLUG,
            '=',
            $sanity
        )->first();

        if ($data) {
            $c = 0;
            if (preg_match('/(?P<slug>.+)\-(?P<inc>[0-9])+$/', $sanity, $match)
                && ! empty($match['inc'])
            ) {
                $sanity = $match['slug'];
                $c      = $match['inc'];
            }
            while ($data) {
                $c++;
                $sanity    = substr($sanity, 0, $length - ($c + 1));
                $newSanity = "{$sanity}-{$c}";
                $data      = self::where(
                    self::COLUMN_RECIPE_SLUG,
                    '=',
                    $newSanity
                )->first();
            }
        }

        self::$performCheckSlug = false;
        return $newSanity;
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function performCheckSlug(array $attributes) : array
    {
        $slug = isset($attributes[self::COLUMN_RECIPE_SLUG])
                && is_string($attributes[self::COLUMN_RECIPE_SLUG])
            ? $attributes[self::COLUMN_RECIPE_SLUG]
            : null;
        if (is_null($slug)) {
            $slug = isset($attributes[self::COLUMN_RECIPE_TITLE])
                    && is_string($attributes[self::COLUMN_RECIPE_TITLE])
                ? $attributes[self::COLUMN_RECIPE_TITLE]
                : '';
        }

        $attributes[self::COLUMN_RECIPE_SLUG] = $this->checkUniqueSlug($slug);
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes[self::COLUMN_RECIPE_SLUG])) {
            $attributes = $this->performCheckSlug($attributes);
            $this[self::COLUMN_RECIPE_SLUG] = $attributes[self::COLUMN_RECIPE_SLUG];
        }

        return parent::save($options);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (!empty($attributes)) {
            $attributes = $this->performCheckSlug($attributes);
        }

        return parent::update($attributes, $options);
    }

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

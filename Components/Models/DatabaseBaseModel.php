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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PentagonalProject\App\Rest\Util\Sanitizer;

/**
 * Sanitize Per Execution Standard SQL as Serialized String if contains of non serialized
 *
 * Class DatabaseBaseModel
 * @package PentagonalProject\Model
 *
 * // list magic method @uses Builder
 *
 * @method static Collection|static[]|static|null find($id, array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Model\|Collection findMany(mixed $id, array $columns = ['*'])
 * @method static Collection|Model findOrFail($id, $columns = ['*'])
 * @method static Model create(array $attributes = [])
 * @method static Model findOrNew($id, $columns = ['*'])
 * @method static Model firstOrNew(array $attributes, array $values = [])
 * @method static Model firstOrCreate(array $attributes, array $values = [])
 * @method static Model updateOrCreate(array $attributes, array $values = [])
 * @method static Model|static firstOrFail($columns = ['*'])
 * @method static Model|static|mixed firstOr($columns = ['*'], \Closure $callback = null)
 * @method static mixed value($column)
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static static[]|Collection get($columns = ['*'])
 * ... @method static mixed *
 *
 * @uses Builder for more usage
 */
class DatabaseBaseModel extends Model
{
    const REGEX_DATE_U = '/^(\d{4})-(\d{1,2})-(\d{1,2})\s+(\d{1,2})\:(\d{1,2})\:(\d{1,2})(\.\d+)?$/';

    /**
     * @var string[] column that PostGreSQL Date TIMESTAMP possible
     * Y-m-d H:i:s.u
     */
    protected $checkDateFormat = [
        self::UPDATED_AT,
        self::CREATED_AT
    ];

    /**
     * @var array
     */
    protected $noFixation = [
        self::UPDATED_AT,
        self::CREATED_AT
    ];

    /**
     * @param mixed $values
     * @return mixed
     */
    public static function resolveResult($values)
    {
        return Sanitizer::maybeUnSerialize($values);
    }

    /**
     * @param mixed $values
     * @return mixed
     */
    public static function resolveSet($values)
    {
        return Sanitizer::maybeSerialize($values);
    }

    /**
     * TimeStamp Resolver
     *
     * @param string $value
     *
     * @return string  with Y-m-d H:i:s
     */
    public function fixMaybeTimeStamp($value)
    {
        if (is_string($value) && preg_match(self::REGEX_DATE_U, $value, $match)) {
            /**
             * will return Y-m-d H:i:s
             */
            $value = date('Y-m-d H:i:s', strtotime($value));
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     * Perform UnSerialize
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->checkDateFormat)) {
                $attributes[$key] = $this->fixMaybeTimeStamp($value);
                continue;
            }

            if (in_array($key, $this->noFixation)) {
                continue;
            }

            $attributes[$key] = $this->resolveResult($value);
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return Model|static
     */
    public function setAttribute($key, $value)
    {
        return parent::setAttribute($key, $this->resolveSet($value));
    }

    ///**
    // * {@inheritdoc}
    // * Perform Serialize
    // */
    //public function getDirty()
    //{
    //    $dirty = parent::getDirty();
    //    foreach ($dirty as $key => $value) {
    //        $dirty[$key] = $this->resolveResult($value);
    //    }
    //
    //    return $dirty;
    //}

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        $value = parent::getAttributeFromArray($key);
        if (in_array($key, $this->noFixation)) {
            return $value;
        }
        return $this->resolveResult($value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttributeForArray($key, $value)
    {
        $result = parent::mutateAttributeForArray($key, $value);
        if (is_array($result)) {
            foreach ($result as $key => $v) {
                if (in_array($key, $this->noFixation)) {
                    continue;
                }
                $result[$key] = $this->resolveResult($v);
            }
        }

        return $result;
    }

    ///**
    // * {@inheritdoc}
    // * Perform Serialize
    // */
    //protected function performInsert(Builder $query)
    //{
    //   $attributes = $this->attributes;
    //   foreach ($attributes as $key => $value) {
    //       $this->attributes[$key] = $this->resolveSet($value);
    //   }

    //   $return_value = parent::performInsert($query);
    //   // re set attributes
    //   $this->attributes = $attributes;
    //   return $return_value;
    //}
}

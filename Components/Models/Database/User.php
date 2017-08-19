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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PentagonalProject\Model\DatabaseBaseModel;

/**
 * Class User
 * @package PentagonalProject\Model\Database
 */
class User extends DatabaseBaseModel
{

    /*-------------------------------------
     * TABLE NAME DEFINITION
     * ----------------------------------- */
    const TABLE_NAME         = 'users';

    /*-------------------------------------
     * COLUMN DEFINITION
     * ----------------------------------- */
    const COLUMN_ID          = 'id';
    const COLUMN_FIRST_NAME  = 'first_name';
    const COLUMN_LAST_NAME   = 'last_name';
    const COLUMN_USERNAME    = 'username';
    const COLUMN_EMAIL       = 'email';
    const COLUMN_PASSWORD    = 'password';
    const COLUMN_PRIVATE_KEY = 'private_key';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    /*-------------------------------------
     * RELATION DEFINITION
     * ----------------------------------- */
    /**
     * @see meta();
     */
    const RELATION_META = 'meta';

    /*-------------------------------------
     * OVERRIDE
     * ----------------------------------- */
    const CREATED_AT = self::COLUMN_CREATED_AT;
    const UPDATED_AT = self::COLUMN_UPDATED_AT;

    /**
     * Enable Time Stamp
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * {@inheritdoc}
     * @var array
     */
    protected $fillable = [
        self::COLUMN_FIRST_NAME,
        self::COLUMN_LAST_NAME,
        self::COLUMN_USERNAME,
        self::COLUMN_EMAIL,
        self::COLUMN_PASSWORD,
        self::COLUMN_PRIVATE_KEY
    ];

    /**
     * @return Collection
     */
    public function getAllMeta() : Collection
    {
        /**
         * @var Builder $meta
         */
        return $this->meta()->getResults();
    }

    /**
     * Get Relational Meta
     *
     * @return HasMany
     */
    public function meta() : HasMany
    {
        return $this->hasMany(
            UserMetaByUserId::class,
            UserMetaByUserId::COLUMN_USER_ID,
            User::COLUMN_ID
        );
    }
}

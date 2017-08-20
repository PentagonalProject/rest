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
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pentagonal\PhPass\PasswordHash;
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

    /*-------------------------------------
     * OVERRIDE
     * ----------------------------------- */
    const PASSWORD_VALID = true;
    const PASSWORD_INVALID_DATA_TYPE = 1;
    const PASSWORD_SHA1 = 2;
    const PASSWORD_PLAIN = 3;

    /**
     * @param string $password
     * @return bool
     */
    public static function validatePasswordType($password)
    {
        $passwordHash = new PasswordHash();
        if ($passwordHash->isMaybeHash($password)) {
            return self::PASSWORD_VALID;
        }

        if (!is_string($password)) {
            return self::PASSWORD_INVALID_DATA_TYPE;
        }

        if (strlen($password) === 40 && !preg_match('/[^a-f0-9]/', $password)) {
            return self::PASSWORD_SHA1;
        }

        return self::PASSWORD_PLAIN;
    }

    /**
     * {@inheritdoc}
     * Perform UnSerialize
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        if (array_key_exists(self::COLUMN_PASSWORD, $attributes)) {
            $type = $this->validatePasswordType($attributes[self::COLUMN_PASSWORD]);
            if ($type !== self::PASSWORD_VALID) {
                if ($type === self::PASSWORD_INVALID_DATA_TYPE) {
                    $attributes[self::COLUMN_PASSWORD] = uniqid(mt_rand(), true);
                    $type = self::PASSWORD_PLAIN;
                }
                if ($type === self::PASSWORD_PLAIN) {
                    $attributes[self::COLUMN_PASSWORD] = sha1($attributes[self::COLUMN_PASSWORD]);
                    $type                              = self::PASSWORD_SHA1;
                }
                if ($type === self::PASSWORD_SHA1) {
                    $passwordHash = new PasswordHash();
                    $attributes[self::COLUMN_PASSWORD] = $passwordHash->hash($attributes[self::COLUMN_PASSWORD]);
                }
                if ($this->exists) {
                    $object = clone $this;
                    $object->update([
                        self::COLUMN_PASSWORD => $attributes[self::COLUMN_PASSWORD],
                        self::UPDATED_AT => $attributes[self::COLUMN_UPDATED_AT]
                    ]);
                }
            }
        }

        return parent::setRawAttributes($attributes, $sync);
    }

    /**
     * @return Collection
     */
    public function getAllMeta() : Collection
    {
        return $this->meta()->getResults();
    }

    /**
     * @return mixed
     */
    public function getMetaDataAll() : Collection
    {
        /**
         * @var Builder $meta
         */
        $meta = $this->meta();
        return $meta->pluck(
            UserMetaByUserId::COLUMN_NAME,
            UserMetaByUserId::COLUMN_VALUE
        );
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

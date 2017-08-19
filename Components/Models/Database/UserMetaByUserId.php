<?php
namespace PentagonalProject\Model\Database;

/**
 * Class UserMetaByUserId
 * @package PentagonalProject\Model\Database
 */
class UserMetaByUserId extends UserMeta
{
    /**
     * Set Primary Key as 'user_id'
     * @var string
     */
    protected $primaryKey = self::COLUMN_USER_ID;

    /**
     * Override
     * @var string
     */
    protected $keyType = 'int';
}

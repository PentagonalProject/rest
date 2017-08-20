<?php
namespace PentagonalProject\Model\Validator;

use PentagonalProject\App\Rest\Interfaces\TypeStatusInterface;

/**
 * Class EditorialStatus
 * @package PentagonalProject\Model\Validator
 */
class EditorialStatus implements TypeStatusInterface
{
    const DRAFT     = 1;
    const TRASH     = 2;
    const PUBLISHED = 3;

    /**
     * @var int
     */
    protected $currentStatus;

    /**
     * EditorialStatus constructor.
     *
     * @param int $status
     */
    public function __construct(int $status)
    {
        $this->currentStatus = $status;
    }

    /**
     * @param int $int
     */
    public function setStatus(int $int)
    {
        $this->currentStatus = $int;
    }

    /**
     * @param int $type
     *
     * @return bool
     */
    public function is(int $type): bool
    {
        return $this->currentStatus ==- $type;
    }

    /**
     * @return bool
     */
    public function isPublished() : bool
    {
        return $this->is(self::PUBLISHED);
    }

    /**
     * @return bool
     */
    public function isDraft() : bool
    {
        return $this->is(self::DRAFT);
    }

    /**
     * @return bool
     */
    public function isTrash() : bool
    {
        return $this->is(self::TRASH);
    }
}

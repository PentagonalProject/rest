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

namespace PentagonalProject\Model\Handler;

use PentagonalProject\Model\Database\User;

/**
 * Class UserRole
 * @package PentagonalProject\Model\Handler
 */
class UserRole
{
    const ROLE_UNKNOWN = 0;
    const ROLE_META_SELECTOR = 'user_role';
    const STATUS_META_SELECTOR = 'user_status';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Role
     */
    protected $role;

    /**
     * @var string|int
     */
    protected $currentRole;

    /**
     * @var string
     */
    protected $currentStatus;

    /**
     * UserRole constructor.
     *
     * @param User $user
     * @param Role $role
     */
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }

    /**
     * @return int|string
     */
    public function getRole()
    {
        if (isset($this->currentRole)) {
            return $this->currentRole;
        }

        $role = $this->user->getMetaValueOrCreate(self::ROLE_META_SELECTOR, self::ROLE_UNKNOWN);
        if (!is_string($role) || ! $this->role->roleExist($role)) {
            return $this->currentRole = self::ROLE_UNKNOWN;
        }

        if (strtolower($role) != $role) {
            $this->user->updateMeta(self::ROLE_META_SELECTOR, strtolower($role));
        }

        return $this->currentRole = strtolower($role);
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        if (isset($this->currentStatus)) {
            return $this->currentStatus;
        }

        $status = $this->user->getMetaValueOrCreate(self::STATUS_META_SELECTOR, Role::STATUS_UNKNOWN);
        if (!is_string($status) || ! $this->role->statusExist($status)) {
            return $this->currentStatus = Role::STATUS_UNKNOWN;
        }

        if (strtolower($status) != $status) {
            $this->user->updateMeta(self::STATUS_META_SELECTOR, strtolower($status));
        }

        return $this->currentStatus = strtolower($status);
    }

    /**
     * @param string|UserRole|User $status
     *
     * @return bool
     */
    public function statusIs($status) : bool
    {
        $c = $this;
        if ($status instanceof UserRole) {
            $c = $status;
            $status = $c->getStatus();
        } elseif ($status instanceof User) {
            $c = new static($status, $this->role);
            $status = $c->getStatus();
        }

        $status = strtolower(trim($status));
        if ($status == '') {
            return false;
        }

        if ($c->isSuperAdmin()) {
            return $status == Role::STATUS_ACTIVE;
        }

        return $c->getStatus() === $status;
    }

    /**
     * @return bool
     */
    public function isSuperAdmin() : bool
    {
        $status = $this->getRole() === Role::ROLE_SUPER_ADMIN;

        // super admin always active
        if ($status && $this->getStatus() != Role::STATUS_ACTIVE) {
            $this->user->updateMeta(self::STATUS_META_SELECTOR, Role::STATUS_ACTIVE);
        }

        return $status;
    }

    /**
     * Whether Super admin is also admin
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->getRole() === Role::ROLE_ADMIN;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->statusIs(Role::STATUS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isBanned() : bool
    {
        return $this->statusIs(Role::STATUS_BANNED);
    }

    /**
     * @return bool
     */
    public function isPending() : bool
    {
        return $this->statusIs(Role::STATUS_PENDING);
    }

    /**
     * @return bool
     */
    public function isWait() : bool
    {
        return $this->statusIs(Role::STATUS_WAIT);
    }

    /**
     * @return bool
     */
    public function isUnknown() : bool
    {
        return $this->statusIs(Role::STATUS_UNKNOWN);
    }
}

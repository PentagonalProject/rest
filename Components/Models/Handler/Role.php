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

use PentagonalProject\App\Rest\Record\AppFacade;

/**
 * Class Role
 * @package PentagonalProject\Model\Handler
 */
class Role
{
    const ROLE_SUPER_ADMIN = 'superadmin';
    const ROLE_ADMIN       = 'admin';
    const ROLE_MEMBER      = 'member';
    const ROLE_SUBSCRIBER  = 'subscriber';
    const ROLE_UNKNOWN     = 'unknown';
    const DEFAULT_ROLE     = self::STATUS_UNKNOWN;

    // pending activation
    const STATUS_UNKNOWN   = 'unknown';
    const STATUS_PENDING   = 'pending';
    // wait approval
    const STATUS_WAIT      = 'wait';
    const STATUS_DEFAULT   = self::STATUS_UNKNOWN;
    const STATUS_ACTIVE    = 'active';
    const STATUS_BANNED    = 'banned';

    /**
     * @var array
     */
    protected $defaultRoleList = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_MEMBER,
        self::ROLE_SUBSCRIBER,
    ];

    /**
     * @var array
     */
    protected $roleList = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_MEMBER
    ];

    /**
     * @var array
     */
    protected $defaultStatusList = [
        self::STATUS_UNKNOWN,
        self::STATUS_PENDING,
        self::STATUS_WAIT,
        self::STATUS_ACTIVE,
        self::STATUS_BANNED
    ];

    /**
     * @var array
     */
    protected $statusList = [
        self::STATUS_UNKNOWN,
        self::STATUS_PENDING,
        self::STATUS_WAIT,
        self::STATUS_ACTIVE,
        self::STATUS_BANNED
    ];

    /**
     * @var string
     */
    protected $defaultRole = self::DEFAULT_ROLE;

    /**
     * @var string
     */
    protected $defaultStatus = self::STATUS_DEFAULT;

    /**
     * @param string $lang
     *
     * @return mixed
     */
    private function trans(string $lang)
    {
        return AppFacade::access('lang')->trans($lang);
    }

    /**
     * @param string $role
     */
    public function addRole(string $role)
    {
        $role = strtolower(trim($role));
        if ($role == '') {
            throw new \InvalidArgumentException(
                $this->trans('Role could not be empty')
            );
        }
        if (!in_array($role, $this->getDefaultRoleList())) {
            $this->roleList[] = $role;
        }
    }

    /**
     * @param string $role
     */
    public function removeRole(string $role)
    {
        $role = strtolower(trim($role));
        if (!in_array($role, $this->getDefaultRoleList())) {
            unset($this->roleList[$role]);
        }
    }

    /**
     * @param string $status
     */
    public function addStatus(string $status)
    {
        $status = strtolower(trim($status));
        if ($status == '') {
            throw new \InvalidArgumentException(
                $this->trans('Status could not be empty')
            );
        }
        if (!in_array($status, $this->getDefaultStatusList())) {
            $this->statusList[] = $status;
        }
    }

    /**
     * @param string $status
     */
    public function removeStatus(string $status)
    {
        $status = strtolower(trim($status));
        if (!in_array($status, $this->getDefaultStatusList())) {
            unset($this->statusList[$status]);
        }
    }

    /**
     * @return array
     */
    public function getRoleList(): array
    {
        return $this->roleList;
    }

    /**
     * @return array
     */
    public function getStatusList() : array
    {
        return $this->statusList;
    }

    /**
     * @return array
     */
    public function getDefaultRoleList(): array
    {
        return $this->defaultRoleList;
    }

    /**
     * @return array
     */
    public function getDefaultStatusList(): array
    {
        return $this->defaultStatusList;
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function roleExist(string $role) : bool
    {
        $role = strtolower(trim($role));
        return in_array($role, $this->getRoleList());
    }

    /**
     * @param string $status
     *
     * @return bool
     */
    public function statusExist(string $status) : bool
    {
        $status = strtolower(trim($status));
        return in_array($status, $this->getStatusList());
    }

    /**
     * @return string
     */
    public function getDefaultRole(): string
    {
        return $this->defaultRole;
    }

    /**
     * @param string $defaultRole
     */
    public function setDefaultRole(string $defaultRole)
    {
        $defaultRole = trim(strtolower($defaultRole));
        if ($defaultRole == '') {
            throw new \InvalidArgumentException(
                $this->trans('Default Role could not be empty')
            );
        }
        if (!$this->roleExist($defaultRole)) {
            throw new \RuntimeException(
                $this->trans('Role not exists'),
                E_WARNING
            );
        }
        $this->defaultRole = $defaultRole;
    }

    /**
     * @return string
     */
    public function getDefaultStatus(): string
    {
        return $this->defaultStatus;
    }

    /**
     * @param string $defaultStatus
     */
    public function setDefaultStatus(string $defaultStatus)
    {
        $defaultStatus = trim(strtolower($defaultStatus));
        if ($defaultStatus == '') {
            throw new \InvalidArgumentException(
                $this->trans('Default Status could not be empty')
            );
        }
        if (!$this->statusExist($defaultStatus)) {
            throw new \RuntimeException(
                $this->trans('Status not exists'),
                E_WARNING
            );
        }

        $this->defaultRole = $defaultStatus;
    }
}

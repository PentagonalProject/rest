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

namespace PentagonalProject\Model\Validator;

use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PentagonalProject\Model\Database\User;
use PentagonalProject\Model\Handler\Role;
use PentagonalProject\Model\Handler\UserAuthenticator;
use PentagonalProject\Model\Handler\UserRole;

/**
 * Class AccessValidator
 * @package PentagonalProject\Model\Validator
 */
class AccessValidator
{
    const API_ACCESS_SELECTOR = 'api_access';

    const LEVEL_GET    = 0;
    const LEVEL_UPDATE = 1;
    const LEVEL_DELETE = 2;
    const LEVEL_CREATE = 3;

    /**
     * @var int
     */
    private $user;

    /**
     * @var int
     */
    private $level;

    /**
     * AccessValidator constructor.
     *
     * @param User $user
     * @param int $level
     */
    private function __construct(User $user, int $level)
    {
        $this->user = $user;
        $this->level = $level;
    }

    /**
     * Check the given request and access level
     *
     * @param User $user
     * @param int $level
     */
    public static function check(User $user, int $level)
    {
        $accessValidator = new static($user, $level);
        $accessValidator->run();
    }

    /**
     * Run the validator
     *
     * @throws UnauthorizedException
     */
    private function run()
    {
        $defaultGrant = [self::LEVEL_GET];
        $adminGrant = [
            self::LEVEL_GET,
            self::LEVEL_UPDATE,
            self::LEVEL_DELETE,
            self::LEVEL_CREATE,
        ];

        // Find the user granted accesses
        $grantedAccesses = $this
            ->user
            ->getMetaValueOrCreate(self::API_ACCESS_SELECTOR, $defaultGrant);
        // create dummy role for super admin
        $dummyRole = new UserRole($this->user, new Role());
        // allow super admin grant as all
        if ($dummyRole->isSuperAdmin()) {
            $defaultGrant = $adminGrant;
        }

        // if has no granted access or grant is super admin & is not same value for default
        if (!is_array($grantedAccesses) || $dummyRole->isSuperAdmin() && $defaultGrant !== $grantedAccesses) {
            $this->user->updateMeta(self::API_ACCESS_SELECTOR, $defaultGrant);
            $grantedAccesses = $defaultGrant;
        }

        // Check whether the given level access is listed in the user
        // granted accesses
        if (empty($grantedAccesses) || ! in_array($this->level, $grantedAccesses, true)) {
            UserAuthenticator::throwUnauthorized();
        };
    }
}

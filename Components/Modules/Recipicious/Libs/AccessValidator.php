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

namespace PentagonalProject\Modules\Recipicious\Lib;

use PentagonalProject\App\Rest\Exceptions\UnauthorizedException;
use PentagonalProject\Model\Database\UserMeta;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AccessValidator
 * @package PentagonalProject\Modules\Recipicious\Lib
 */
class AccessValidator
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $level;

    /**
     * AccessValidator constructor
     *
     * @param ServerRequestInterface $request
     * @param int                    $level
     */
    private function __construct(ServerRequestInterface $request, int $level)
    {
        $this->userId = (int) $request->getParsedBody()['user_id'];
        $this->level = $level;
    }

    /**
     * Check the given request and access level
     *
     * @param ServerRequestInterface $request
     * @param int                    $level
     */
    public static function check(ServerRequestInterface $request, int $level)
    {
        $accessValidator = new static($request, $level);
        $accessValidator->run();
    }

    /**
     * Run the validator
     *
     * @throws UnauthorizedException
     */
    private function run()
    {
        // Find the user granted accesses
        $grantedAccesses = UserMeta::where(
            'user_id',
            $this->userId
        )
            ->where('meta_name', 'api_access')
            ->first();

        // Check whether the given level access is listed in the user
        // granted accesses
        if (!in_array($this->level, $grantedAccesses->meta_value)) {
            throw new UnauthorizedException(
                "Not enough access"
            );
        };
    }
}

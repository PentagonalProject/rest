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

use Slim\Http\Cookies;

/**
 * Class CookieSession
 * @package PentagonalProject\Model
 * @method void set(string $name, string|array $value)
 * @method mixed get(string $name, $default = null)
 * @method void setDefaults(array $settings)
 * @method string[] toHeaders()
 * - static method
 * @method static array parseHeader(string $header)
 */
class CookieSession
{
    /**
     * @var Cookies
     */
    protected $cookies;

    /**
     * CookieSession constructor.
     *
     * @param Cookies $cookies
     */
    public function __construct(Cookies $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Set Cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $hostOnly
     */
    public function setCookie(
        string $name,
        string $value,
        $expires = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false,
        $hostOnly = false
    ) {
        $this->cookies->set(
            $name,
            [
                'value' => $value,
                'expires' => $expires,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'hostonly' => $hostOnly,
            ]
        );
    }

    /**
     * Magic Method
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->cookies, $name], $arguments);
    }

    /**
     * Magic Method
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return call_user_func_array([Cookies::class, $name], $arguments);
    }
}

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

namespace PentagonalProject\App\Rest\Util;

use Monolog\Logger;

/**
 * Class Validator
 * @package PentagonalProject\App\Rest\Util
 *
 * Useful Helper
 */
class Validator
{
    /* SERVER TYPE CONSTANT
     * ----------------------------- */
    const SERVER_TYPE_APACHE = 'apache';
    const SERVER_TYPE_LITESPEED = 'litespeed';
    const SERVER_TYPE_NGINX = 'nginx';
    const SERVER_TYPE_IIS = 'iis';
    const SERVER_TYPE_IIS7 = 'iis7';
    const SERVER_TYPE_HIAWATHA = 'hiawatha';
    const SERVER_TYPE_LIGHTTPD = 'lighttpd';
    const SERVER_TYPE_UNKNOWN = '';

    /**
     * Get Web Server Type
     *
     * @return string
     */
    public static function getWebServerSoftWare() : string
    {
        static $type;

        if (isset($type)) {
            return $type;
        }

        $software = isset($_SERVER['SERVER_SOFTWARE'])
            ? $_SERVER['SERVER_SOFTWARE']
            : null;

        $type = self::SERVER_TYPE_UNKNOWN;

        if (stripos($software, 'lighttpd') !== false) {
            $type = self::SERVER_TYPE_LIGHTTPD;
        }

        if (strpos($software, 'Hiawatha') !== false) {
            $type = self::SERVER_TYPE_HIAWATHA;
        }

        if (strpos($software, 'Apache') !== false) {
            $type = self::SERVER_TYPE_APACHE;
        } elseif (strpos($software, 'Litespeed') !== false) {
            $type = self::SERVER_TYPE_LITESPEED;
        }

        if (strpos($software, 'nginx') !== false) {
            $type = self::SERVER_TYPE_NGINX;
        }

        if ($type !== self::SERVER_TYPE_APACHE && $type !== self::SERVER_TYPE_LITESPEED
            && strpos($software, 'Microsoft-IIS') !== false
            && strpos($software, 'ExpressionDevServer') !== false
        ) {
            $type = self::SERVER_TYPE_IIS;
            if (intval(substr($software, strpos($software, 'Microsoft-IIS/')+14)) >= 7) {
                $type = self::SERVER_TYPE_IIS7;
            }
        }
        if (function_exists('apache_get_modules')) {
            if (in_array('mod_security', apache_get_modules())) {
                $type = self::SERVER_TYPE_APACHE;
            }

            if ($type == self::SERVER_TYPE_UNKNOWN && function_exists('apache_get_version')) {
                $type = self::SERVER_TYPE_APACHE;
            }
        }

        return $type;
    }

    /**
     * Check if apache Server Base
     *
     * @return bool
     */
    public static function isApache() : bool
    {
        return in_array(self::getWebServerSoftWare(), [self::SERVER_TYPE_APACHE, self::SERVER_TYPE_LITESPEED]);
    }

    /**
     * Check if Litespeed Web Server Base
     *
     * @return bool
     */
    public static function isLiteSpeed() : bool
    {
        return self::getWebServerSoftWare() === self::SERVER_TYPE_LIGHTTPD;
    }

    /**
     * Check if Nginx Web Server Base
     *
     * @return bool
     */
    public static function isNGinX() : bool
    {
        return self::getWebServerSoftWare() === self::SERVER_TYPE_NGINX;
    }

    /**
     * Check if Hiawatha Web Server Base
     *
     * @return bool
     */
    public static function isHiawatha() : bool
    {
        return self::getWebServerSoftWare() === self::SERVER_TYPE_HIAWATHA;
    }

    /**
     * Check if Hiawatha Web Server Base
     *
     * @return bool
     */
    public static function isLigHTTPd() : bool
    {
        return self::getWebServerSoftWare() === self::SERVER_TYPE_LIGHTTPD;
    }

    /**
     * Check if IIS Web Server Base
     *
     * @return bool
     */
    public static function isIIS() : bool
    {
        return in_array(self::getWebServerSoftWare(), [self::SERVER_TYPE_IIS, self::SERVER_TYPE_IIS7]);
    }

    /**
     * Check if IIS7 Web Server Base
     *
     * @return bool
     */
    public static function isIIS7() : bool
    {
        return self::getWebServerSoftWare() === self::SERVER_TYPE_IIS7;
    }

    /**
     * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
     *
     * @return bool
     */
    public static function isMobile() : bool
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'Mobile') !== false // many mobile devices (all iPhone, iPad, etc.)
            || strpos($userAgent, 'Android') !== false
            || strpos($userAgent, 'Silk/') !== false
            || strpos($userAgent, 'Kindle') !== false
            || strpos($userAgent, 'BlackBerry') !== false
            || strpos($userAgent, 'Opera Mini') !== false
            || strpos($userAgent, 'Opera Mobi') !== false
        ) {
            return true;
        }

        return false;
    }
    /**
     * Check If Using PhPass from
     * {@link http://www.openwall.com/phpass}
     *
     * @param string $string has to check
     * @return bool
     */
    public static function isMaybeOpenWallPasswordHash($string) : bool
    {
        if (!is_string($string)
            || ! in_array(($length = strlen($string)), [20, 34, 60])
            || preg_match('/[^a-zA-Z0-9\.\/\$\_]/', $string)
        ) {
            return false;
        }

        switch ((string) $length) {
            case '20':
                return !($string[0] != '_'
                    || strpos($string, '$') !== false
                    || strpos($string, '.') === false
                );
            case '34':
                return !(substr_count($string, '$') <> 2
                    || !in_array(substr($string, 0, 3), ['$P$', '$H$'])
                );
        }

        return !(
            substr($string, 0, 4) != '$2a$'
            || substr($string, 6, 1) != '$'
            || ! is_numeric(substr($string, 4, 2))
            || substr_count($string, '$') <> 3
        );
    }

    /**
     * Get Alias Log Level
     *
     * @param int $code
     * @return int if no match between code will be return int 0
     */
    public static function getConvertAliasLogLevel(int $code) : int
    {
        switch ($code) {
            case Logger::ALERT:
                return Logger::ALERT;
            case Logger::CRITICAL:
                return Logger::CRITICAL;
            case E_ALL:
            case Logger::DEBUG:
                return Logger::DEBUG;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case Logger::NOTICE:
                return Logger::NOTICE;
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case Logger::ERROR:
                return Logger::ERROR;
            case Logger::INFO:
                return Logger::INFO;
            case Logger::EMERGENCY:
                return Logger::EMERGENCY;
            case E_WARNING:
            case E_USER_WARNING:
            case E_COMPILE_WARNING:
            case Logger::WARNING:
                return Logger::WARNING;
        }

        return 0;
    }

    /**
     * Get Default Log Name By Code
     *
     * @param int $code
     * @return string|null string as lowercase and returning null if not found
     */
    public static function getLogStringByCode($code)
    {
        $code = is_numeric($code) && !is_float($code)
            ? abs($code)
            : $code;
        if (!is_int($code)) {
            return null;
        }

        switch (self::getConvertAliasLogLevel($code)) {
            case Logger::NOTICE:
                return 'notice';
            case Logger::ERROR:
                return 'error';
            case Logger::WARNING:
                return 'warning';
            case Logger::INFO:
                return 'info';
            case Logger::DEBUG:
                return 'debug';
            case Logger::EMERGENCY:
                return 'emergency';
            case Logger::CRITICAL:
                return 'critical';
            case Logger::ALERT:
                return 'alert';
        }

        return null;
    }

    /**
     * Test if a give filesystem path is absolute.
     *
     * For example, '/foo/bar', or 'c:\windows'.
     *
     * @since 2.5.0
     *
     * @param string $path File path.
     * @return bool True if path is absolute, false is not absolute.
     */
    public static function isAbsolutePath($path) : bool
    {
        /*
         * This is definitive if true but fails if $path does not exist or contains
         * a symbolic link.
         */
        if (realpath($path) == $path) {
            return true;
        }

        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        // Windows allows absolute paths like this.
        if (preg_match('#^[a-zA-Z]:\\\\#', $path)) {
            return true;
        }

        // A path starting with / or \ is absolute; anything else is relative.
        return ( $path[0] == '/' || $path[0] == '\\' );
    }
}

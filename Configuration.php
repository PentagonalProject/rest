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

/* ------------------------------------------------------ *\
 |                        CONFIG                          |
\* ------------------------------------------------------ */
/**
 * Use @const WEB_ROOT to get Public / Web Directory
 */
return [
    'directory' => [
        'storage'   => __DIR__ . '/Storage/',
        'module'    => __DIR__ . '/Components/Modules/',
        'extension' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Extensions',
    ],
    'database' => [
        'driver'    => 'mysql',
        // example database host
        'host'      => isset($_SERVER['OPENSHIFT_MYSQL_DB_HOST'])
            ? $_SERVER['OPENSHIFT_MYSQL_DB_HOST']
            : 'localhost',
        'port'      => isset($_SERVER['OPENSHIFT_MYSQL_DB_PORT'])
            ? $_SERVER['OPENSHIFT_MYSQL_DB_PORT']
            : 3306,
        // example database user
        'username'  => isset($_SERVER['OPENSHIFT_MYSQL_DB_USERNAME'])
            ? $_SERVER['OPENSHIFT_MYSQL_DB_USERNAME']
            : 'root',
        // example database user password
        'password'  => isset($_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD'])
            ? $_SERVER['OPENSHIFT_MYSQL_DB_PASSWORD']
            : 'mysql',
        'charset'   => 'utf8',
        // Database Name
        'database'  => isset($_SERVER['OPENSHIFT_MYSQL_DB_NAME'])
            ? $_SERVER['OPENSHIFT_MYSQL_DB_NAME']
            : 'recipicious',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ],
    'cache'      => [
        // driver name
        'driver' => 'redis',
        /**
         * @see \phpFastCache\CacheManager::getDefaultConfig()
         */
        'config' => [
            /**
             * Redis Config
             */
            'port' => isset($_SERVER['OPENSHIFT_REDIS_PORT'])
                ? $_SERVER['OPENSHIFT_REDIS_PORT']
                : null,
            'host' => isset($_SERVER['OPENSHIFT_REDIS_HOST'])
                ? $_SERVER['OPENSHIFT_REDIS_HOST']
                : '127.0.0.1',
            'password' => isset($_SERVER['OPENSHIFT_REDIS_PASSWORD'])
                ? $_SERVER['OPENSHIFT_REDIS_PASSWORD']
                : null,
            // common config
            'securityKey' => 'auto',
            'ignoreSymfonyNotice' => false,
            'defaultTtl' => 900,
            'htaccess' => true,
            'default_chmod' => 0777,
            'path' => dirname(__DIR__) . DIRECTORY_SEPARATOR,
            'fallback' => false,
            'limited_memory_each_object' => 4096,
            'compress_data' => false,
        ]
    ],
    'session'  => [
        'name' => null,
        'save_path' => null,
        // values of cookie params
        'path' => '/',
        'lifetime' => 0,
        'domain'   => null,
        'httponly' => null,
        'secure'   => null,
    ],
    // beware debug log will be make your disk full, use this for dev only
    'debug'    => false,
    'log_mode' => E_NOTICE,
    'log_path' => null,
    'displayErrorDetails' => true,
    'determineRouteBeforeAppMiddleware' => false,
];

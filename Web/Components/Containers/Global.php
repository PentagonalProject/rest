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

namespace {

    use PentagonalProject\App\Rest\Util\Sanitizer;
    use PentagonalProject\App\Rest\Util\Validator;
    use Slim\Container;

    /**
     * Build Config
     */
    $dirConfig = dirname(__DIR__);
    $config = (array) require $dirConfig . '/Configuration.php';
    $config['directory'] = isset($config['directory']) ? $config['directory'] : [];
    if (!is_array($config['directory'])) {
        $config['directory'] =  [];
    }

    foreach ($config['directory'] as $key => $value) {
        if (Validator::isAbsolutePath($value)) {
            if (realpath($value) != $value) {
                $value = Sanitizer::normalizePath(realpath($value)?: $value);
            }
        } else {
            $value = Sanitizer::fixDirectorySeparator($value);
            if (strpos($value, '.' . DIRECTORY_SEPARATOR) !== false) {
                if (strpos($value, $dirConfig) === false) {
                    $value = Sanitizer::normalizePath($dirConfig . DIRECTORY_SEPARATOR . $value);
                }
            }
        }
        $config['directory'][$key] = rtrim($value, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    // auto httpVersion
    $config['httpVersion'] = isset($_SERVER['SERVER_PROTOCOL'])
    && strpos($_SERVER['SERVER_PROTOCOL'], '/') !== false
        ? explode('/', $_SERVER['SERVER_PROTOCOL'])[1]
        : '1.1';
    $defaultConfigSetting = [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ];

    $config = array_merge($defaultConfigSetting, $config);
    $container = [
        'settings'    => $config,
        'config'      => require __DIR__ . '/Global/Config.php',
        'hook'        => require __DIR__ . '/Global/Hook.php',
        'environment' => require __DIR__ . '/Global/Environment.php',
        'database'    => require __DIR__ . '/Global/Database.php',
        'notAllowedHandler' => require __DIR__ . '/Global/NotAllowedHandler.php',
        'notFoundHandler'   => require __DIR__ . '/Global/NotFoundHandler.php',
        'phpErrorHandler'   => require __DIR__ . '/Global/PhpErrorHandler.php',
    ];

    return new Container($container);
}
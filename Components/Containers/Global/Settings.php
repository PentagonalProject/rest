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

/**
 * Processing Sanitation on configuration
 */
namespace {

    use PentagonalProject\App\Rest\Record\AppFacade;
    use PentagonalProject\App\Rest\Util\Sanitizer;
    use PentagonalProject\App\Rest\Util\Validator;

    /**
     * @var AppFacade $this
     */
    if (!isset($this) || ! $this instanceof AppFacade) {
        return;
    }

    /**
     * Default Slim Container Settings
     */
    $defaultConfigSetting = [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => false,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
    ];

    /* --------------------------------------------------------------------
     *                          DOING SANITATION
     * ------------------------------------------------------------------- */

    // declare Root Directory
    $rootDir = __DIR__ . '/../../../../';
    $rootDir = realpath($rootDir) ?: $rootDir;
    $config = (array) $this->getArgument('config', []);
    $config['directory'] = isset($config['directory']) ? $config['directory'] : [];
    if (!is_array($config['directory'])) {
        $config['directory'] =  [];
    }

    /**
     * Fix Each Path Directory
     */
    foreach ($config['directory'] as $key => $value) {
        if (!is_string($value)) {
            continue;
        }
        if (Validator::isAbsolutePath($value)) {
            if (realpath($value) != $value) {
                $value = Sanitizer::normalizePath(realpath($value)?: $value);
            }
        } else {
            $value = Sanitizer::fixDirectorySeparator($value);
            if (strpos($value, '.' . DIRECTORY_SEPARATOR) !== false) {
                if (strpos($value, $rootDir) === false) {
                    $value = Sanitizer::normalizePath($rootDir . DIRECTORY_SEPARATOR . $value);
                }
            }
        }

        $config['directory'][$key] = rtrim($value, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    if (empty($config['directory']['module'])) {
        $config['directory']['module'] = Sanitizer::normalizePath(__DIR__ . '/../../../Components/Modules/');
    }
    if (empty($config['directory']['storage'])) {
        $config['directory']['storage'] = Sanitizer::normalizePath(__DIR__ . '/../../../Storage/');
    }

    /**
     * Http Version Protocol Check
     */
    $config['httpVersion'] = isset($_SERVER['SERVER_PROTOCOL'])
    && strpos($_SERVER['SERVER_PROTOCOL'], '/') !== false
        ? explode('/', $_SERVER['SERVER_PROTOCOL'])[1]
        : $defaultConfigSetting['httpVersion'];

    // replace
    return $this
        ->setArgument('config', array_merge($defaultConfigSetting, $config))
        ->getArgument('config');
}

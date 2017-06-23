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

    use Illuminate\Support\Facades\Config;
    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;
    use PentagonalProject\App\Rest\Record\Facade;
    use PentagonalProject\App\Rest\Util\Sanitizer;
    use PentagonalProject\App\Rest\Util\Validator;
    use Psr\Container\ContainerInterface;

    /**
     * @param ContainerInterface $container
     * @return Logger
     */
    return function (ContainerInterface $container) : Logger {
        $logger = new Logger(Facade::current()->getName());
        /**
         * @var Config $config
         */
        $config = $container['config'];
        if ($config['log']) {
            $type = is_int($config['log'])
                ? $config['log']
                : ($config['debug']
                    ? 'deb.log'
                    : Logger::WARNING
                );
            $type = Validator::getConvertAliasLogLevel($type);
            $type = $type > 0 ? $type : 0;
        }

        if (!empty($type)) {
            $logName = $config['log_path'];
            $hasLog = true;
            if (!$logName || !is_string($logName) || trim($logName) == '') {
                $hasLog = false;
                $logName = Validator::getLogStringByCode($type);
                if (!$logName) {
                    $logName =  'log.log';
                }
            }

            $logName = $hasLog ? preg_replace(
                '/(\\\|\/)/',
                DIRECTORY_SEPARATOR,
                $logName
            ) : $logName;

            $selfLog = false;
            if ($hasLog && (
                    DIRECTORY_SEPARATOR == '/' && $logName[0] === DIRECTORY_SEPARATOR ||
                    DIRECTORY_SEPARATOR == '\\' && preg_match('/^([a-z]+)\:\\/i', $logName)
                )
            ) {
                $selfLog = file_exists($logName)
                    || (dirname($logName) !== DIRECTORY_SEPARATOR
                        && is_dir(dirname($logName))
                        && is_writable(dirname($logName))
                    );
            }

            if (! $selfLog) {
                $logName = str_replace(
                    [
                        '/',
                        '\\'
                    ],
                    '_',
                    $logName
                );
                if ($config['directory[storage]']) {
                    $logName = Sanitizer::normalizePath(
                        $config['directory[storage]']
                        . '/logs/'
                        . $logName
                    );
                }
            }

            if ($logName) {
                $config['log_path'] = $logName;
                $logger->pushHandler(
                    new StreamHandler($logName, $type)
                );
            }
        }

        return $logger;
    };
}

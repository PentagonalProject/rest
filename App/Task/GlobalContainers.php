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

    use PentagonalProject\App\Rest\Record\AppFacade;
    use Slim\Container;

    /**
     * @var AppFacade $this
     */
    if (!isset($this[1]) || ! $this[1] instanceof AppFacade) {
        return;
    }

    /**
     * Container Lists
     */
    $container = [
        'settings'    => AppFacade::includeScope(__DIR__ . '/Util/ConfigurationSanity.php', $this[1]),
        'cache'       => AppFacade::includeScope(__DIR__ . '/Containers/Global/Cache.php'),
        'config'      => AppFacade::includeScope(__DIR__ . '/Containers/Global/Config.php'),
        'database'    => AppFacade::includeScope(__DIR__ . '/Containers/Global/Database.php'),
        'environment' => AppFacade::includeScope(__DIR__ . '/Containers/Global/Environment.php'),
        'hook'        => AppFacade::includeScope(__DIR__ . '/Containers/Global/Hook.php'),
        'log'         => AppFacade::includeScope(__DIR__ . '/Containers/Global/Log.php'),
        'module'      => AppFacade::includeScope(__DIR__. '/Containers/Global/Module.php'),
        'notAllowedHandler' => AppFacade::includeScope(__DIR__ . '/Containers/Global/NotAllowedHandler.php'),
        'notFoundHandler'   => AppFacade::includeScope(__DIR__ . '/Containers/Global/NotFoundHandler.php'),
        'phpErrorHandler'   => AppFacade::includeScope(__DIR__ . '/Containers/Global/PhpErrorHandler.php'),
    ];

    return new Container($container);
}

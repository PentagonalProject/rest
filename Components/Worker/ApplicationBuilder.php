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
    use PentagonalProject\App\Rest\Util\ComposerLoaderPSR4;
    use Slim\Container;

    /**
     * @var AppFacade $this
     */
    if (!isset($this) || ! $this instanceof AppFacade) {
        return;
    }

    // register AutoLoader
    $loader = new ComposerLoaderPSR4();
    $loader->add("PentagonalProject\\Exceptions\\", __DIR__ . "/../Exceptions/");
    $loader->add("PentagonalProject\\Model\\", __DIR__ . "/../Models/");
    $loader->register();

    /**
     * Container Lists
     */
    $containerDirectory = dirname(__DIR__) . '/Containers';
    $container = [
        'settings'    => $this->includeScopeBind("{$containerDirectory}/Global/Settings.php", $this),
        'cache'       => $this->includeScope("{$containerDirectory}/Global/Cache.php"),
        'config'      => $this->includeScope("{$containerDirectory}/Global/Config.php"),
        'database'    => $this->includeScope("{$containerDirectory}/Global/Database.php"),
        'environment' => $this->includeScope("{$containerDirectory}/Global/Environment.php"),
        'hook'        => $this->includeScope("{$containerDirectory}/Global/Hook.php"),
        'log'         => $this->includeScope("{$containerDirectory}/Global/Log.php"),
        'module'      => $this->includeScope("{$containerDirectory}/Global/Module.php"),
        'notAllowedHandler' => $this->includeScope("{$containerDirectory}/Global/NotAllowedHandler.php"),
        'notFoundHandler'   => $this->includeScope("{$containerDirectory}/Global/NotFoundHandler.php"),
        'phpErrorHandler'   => $this->includeScope("{$containerDirectory}/Global/PhpErrorHandler.php"),
        'app'         => function () {
            $slim = AppFacade::current()->getAccessor()->getApp();
            return $slim;
        }
    ];

    /**
     * Create Application Accessor
     */
    $facadeAccessor = $this->getAccessor()->create(new Container($container));
    return $facadeAccessor['app'];
}

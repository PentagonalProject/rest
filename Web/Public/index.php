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
    use PentagonalProject\App\Rest\Util\Hook;
    use Slim\App;

    // require BootStrap
    require_once __DIR__ . '/../../Components/bootstrap.php';
    $facade = AppFacade::register('public');
    $facade->setArgument('config', $facade->includeScope(__DIR__ . '/../../Configuration.php'));
    /**
     * AppFacade Scope Returning @uses \Slim\App
     * @var App $app
     */
    $app = $facade->includeScopeBind(__DIR__ . '/../../Components/Worker/Public.php', $facade);

    /**
     * @var Hook $hook
     */
    $response = $app->run(true);
    $container = $app->getContainer();
    if (isset($container['hook']) && $container['hook'] instanceof Hook) {
        $hook     = $container['hook'];
        // hook for end
        $response = $hook->apply('response.end', $response, $container);
    }

    $app->respond($response);
    return $response;
}

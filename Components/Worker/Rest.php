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
    use Slim\App;

    /**
     * @var AppFacade $this
     */
    if (!isset($this) || ! $this instanceof AppFacade) {
        return;
    }

    /**
     * @var App $app
     * Binding @uses AppFacade to Application Builder
     */
    $app = $this->includeScopeBind(__DIR__ . '/ApplicationBuilder.php', $this);
    // Include Files
    $this->includeBindToSlim(__DIR__ . "/../Middlewares/CommonMiddleware.php");
    $this->includeBindToSlim(__DIR__ . "/../Middlewares/RestMiddleware.php");
    // add user routes
    $this->includeBindToSlim(__DIR__ . "/../Routes/UserRoutes.php");

    // return \Slim\App
    return $app;
}

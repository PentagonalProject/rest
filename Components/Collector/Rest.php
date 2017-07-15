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

    use PentagonalProject\App\Rest\Generator\Response\Json;
    use PentagonalProject\App\Rest\Util\ComposerLoaderPSR4;
    use PentagonalProject\Modules\Recipicious\Model\Database\Recipe;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;

    /**
     * @var App $this
     */
    ComposerLoaderPSR4::create([
        "PentagonalProject\\Model\\" => __DIR__ . "/../Models/",
    ])->register();

    // Require Common Middleware
    require_once __DIR__ . '/../Middlewares/CommonMiddleware.php';

    $this->get('/recipes', function (ServerRequestInterface $request, ResponseInterface $response) {
        $responseBuilder = Json::generate($request, $response);
        $responseBuilder->setData(Recipe::all());
        $response = $responseBuilder->serve();
        return $response;
    });
}

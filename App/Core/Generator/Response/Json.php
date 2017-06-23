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

namespace PentagonalProject\App\Rest\Generator\Response;

use PentagonalProject\App\Rest\Abstracts\ResponseGeneratorAbstract;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Json
 * @package PentagonalProject\App\Rest\Generator\Response
 */
class Json extends ResponseGeneratorAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $mimeType = 'application/json';

    /**
     * {@inheritdoc}
     */
    public function serve() : ResponseInterface
    {
        // set Mime Type Override
        $this->setMimeType('application/json');

        /**
         * @var ResponseInterface $response
         */
        $response = $this
            ->getResponse()
            // fall back default to 200 if no status code
            ->withStatus($this->getStatusCode() ?: 200)
            ->withBody($this->createStreamBody())
            ->withHeader('Content-Type', $this->getContentType());
        $response
            ->getBody()
            ->write(
                $json = json_encode($this->getData(), $this->getEncoding())
            );

        // Ensure that the json encoding passed successfully
        if ($json === false) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        if ($response->hasHeader('Content-Length')) {
            $response = $response
                ->withHeader(
                    'Content-Length', $response->getBody()->getSize()
                );
        }

        return $response;
    }
}

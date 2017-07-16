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
use Psr\Http\Message\RequestInterface;
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
     * @param boolean $pretty to use Pretty Printed
     */
    public function serve($pretty = false) : ResponseInterface
    {
        // set Mime Type Override
        $this->setMimeType('application/json');

        if ($pretty) {
            $this->setEncoding(JSON_PRETTY_PRINT);
        }

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
                    'Content-Length',
                    $response->getBody()->getSize()
                );
        }

        return $response;
    }

    /* ------------------------------------------------
                ADDITIONAL FOR IDE COMPLETION
      ------------------------------------------------ */

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public static function generate(RequestInterface $request, ResponseInterface $response) : ResponseGeneratorAbstract
    {
        return parent::generate($request, $response);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setData($data) : ResponseGeneratorAbstract
    {
        return parent::setData($data);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setEncoding(int $encoding): ResponseGeneratorAbstract
    {
        return parent::setEncoding($encoding);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setCharset(string $charset = null) : ResponseGeneratorAbstract
    {
        if (!$charset) {
            return $this;
        }

        return parent::setCharset($charset);
    }
    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setMimeType(string $mimeType) : ResponseGeneratorAbstract
    {
        return parent::setMimeType($mimeType);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setStatusCode($status) : ResponseGeneratorAbstract
    {
        return parent::setStatusCode($status);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setRequest(RequestInterface $request): ResponseGeneratorAbstract
    {
        return parent::setRequest($request);
    }

    /**
     * {@inheritdoc}
     * @return Json|ResponseGeneratorAbstract
     */
    public function setResponse(ResponseInterface $response): ResponseGeneratorAbstract
    {
        return parent::setResponse($response);
    }
}

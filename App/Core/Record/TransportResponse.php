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

namespace PentagonalProject\App\Rest\Record;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use UnexpectedValueException;

/**
 * Class TransportResponse
 * @package PentagonalProject\App\Rest\Record
 *
 * Helper to translate Response
 */
class TransportResponse
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * TransportResponse constructor.
     * @param ResponseInterface|Exception $response
     * @throws InvalidArgumentException
     */
    public function __construct($response)
    {
        if (! $response instanceof Exception && ! $response instanceof ResponseInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    "Invalid Parameter Response. Response must be an instance of %s or %s.",
                    ResponseInterface::class,
                    Exception::class
                )
            );
        }

        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function isError() : bool
    {
        return $this->getResponse() instanceof Exception;
    }

    /**
     * Check if Response is Timed Out
     *
     * @return bool
     */
    public function isTimeOut() : bool
    {
        return $this->isError()
            && stripos($this->getResponse()->getMessage(), 'Timed Out') !== false;
    }

    /**
     * Check Whether Response Is Valid
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->getResponse() instanceof ResponseInterface;
    }

    /**
     * Get The Response Response
     *
     * @return Exception|ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get Response Body
     *
     * @return StreamInterface
     * @throws UnexpectedValueException
     */
    public function getResponseBody() : StreamInterface
    {
        if ($this->isError()) {
            throw new UnexpectedValueException(
                sprintf(
                    'Result is not a valid response, returning exceptions with : <br/> %s',
                    (string) $this->getResponse()
                ),
                E_WARNING
            );
        }

        return $this->response->getBody();
    }

    /**
     * Get Response Body String
     *
     * @return string
     */
    public function getResponseBodyString() : string
    {
        return (string) $this->getResponseBody();
    }
}

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

namespace PentagonalProject\App\Rest\Http;

use Exception;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Uri;

/**
 * Class StreamSocketTransport
 * @package PentagonalProject\App\Rest\Http
 *
 * @method void close();
 * @method resource|null detach();
 * @method int getSize();
 * @method int tell();
 * @method bool eof();
 * @method bool isSeekable();
 * @method void seek($offset, $whence = SEEK_SET);
 * @method void rewind();
 * @method bool isWritable();
 * @method bool|int write(string $string);
 * @method bool isReadable();
 * @method string read(int $length);
 * @method string getContents();
 * @method mixed getMetadata(string $key = null);
 */
class StreamSocketTransport
{
    /**
     * @var Stream
     */
    protected $stream;

    /**
     * @var int
     */
    protected $timeout = 5;

    /**
     * @var int
     */
    protected $errNo;

    /**
     * @var string
     */
    protected $errMessage;

    /**
     * SocketTransport constructor.
     * @param string $uri
     * @param int $timeout
     * @throws Exception
     */
    public function __construct(string $uri, int $timeout = 5)
    {
        $transport = new Uri($uri);
        $this->timeout = $timeout;
        $socket = @fsockopen(
            $transport->getHost(),
            $transport->getPort(),
            $this->errNo,
            $this->errMessage,
            $this->timeout
        );

        if (!$socket) {
            throw new Exception(
                $this->errMessage,
                $this->errNo
            );
        }

        $this->stream = new Stream($socket);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if ($this->stream) {
            return call_user_func_array([$this->stream, $name], $arguments);
        }

        throw new \BadMethodCallException(
            sprintf("Call to undefined method %s.", $name),
            E_USER_ERROR
        );
    }

    /**
     * @return int
     */
    public function getTimeout() : int
    {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getErrNo() : int
    {
        return $this->errNo;
    }

    /**
     * @return string
     */
    public function getErrMessage() : string
    {
        return $this->errMessage;
    }

    /**
     * @return Stream
     */
    public function getStream() : Stream
    {
        return $this->stream;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->stream;
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->stream->close();
        $this->stream = null;
    }
}

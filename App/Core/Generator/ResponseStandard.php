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

namespace PentagonalProject\App\Rest\Generator;

use PentagonalProject\App\Rest\Abstracts\ResponseGeneratorAbstract;
use PentagonalProject\App\Rest\Generator\Response\Html;
use PentagonalProject\App\Rest\Generator\Response\Json;
use PentagonalProject\App\Rest\Generator\Response\Text;
use PentagonalProject\App\Rest\Generator\Response\Xml;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Exception;
use InvalidArgumentException;
use Throwable;

/**
 * Class ResponseStandard
 * @package PentagonalProject\App\Rest\Generator
 */
class ResponseStandard
{
    /**
     * @var ResponseGeneratorAbstract|Json|Xml|Text|Html
     */
    protected $generator;

    /**
     * @var array
     */
    protected $output = [];

    /**
     * @var array
     */
    protected $successCode = [
        //Informational 1xx
        100, // 'Continue',
        101, // 'Switching Protocols',
        102, // 'Processing',
        //Successful 2xx
        200, // 'OK',
        201, // 'Created',
        202, // 'Accepted',
    ];

    /**
     * @var bool
     */
    protected $withTrace = false;

    /**
     * ResponseStandard constructor.
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string|ResponseGeneratorAbstract|null $generator
     * @throws InvalidArgumentException
     */
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        $generator = null
    ) {
        if (!$generator) {
            $generator = new Json($request, $response);
        }

        if (is_string($generator)) {
            if (!class_exists($generator)) {
                throw new InvalidArgumentException(
                    'Class %s does not exists!',
                    E_USER_ERROR
                );
            }
            if (!is_subclass_of($generator, ResponseGeneratorAbstract::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Class %1$s must be sub class of %2$s',
                        $generator,
                        ResponseGeneratorAbstract::class
                    )
                );
            }
            $generator = new $generator($request, $response);
        }

        if (!$generator instanceof ResponseGeneratorAbstract) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument generator must be instance of %s',
                    ResponseGeneratorAbstract::class
                )
            );
        }

        $this->generator = $generator?: new Json($request, $response);
    }

    /**
     * @param mixed $data
     * @return array
     */
    protected function generateData($data) : array
    {
        $output = [
            'code' => $this->generator->getStatusCode(),
            'status' => $this->generator->getReasonPhrase(),
        ];

        // fix error if there was exception
        if ($data instanceof Exception || $data instanceof Throwable) {
            if (in_array($output['code'], $this->successCode)) {
                $this->generator->setStatusCode(500);
                $output['code'] = 500;
                $output['status'] = $this->generator->getReasonPhrase();
            }

            if ($this->isWithTrace()) {
                $output['error']['code'] = $data->getCode();
                $output['error']['line'] = $data->getLine();
                $output['error']['file'] = $data->getFile();
                $output['error']['message'] = $data->getMessage();
                $output['error']['trace'] = $data->getTraceAsString();
                return $output;
            }

            $output['error'] = [
                'message' => $data->getMessage(),
            ];

            return $output;
        }

        if (in_array($output['code'], $this->successCode)) {
            $output['data'] = $data;
            return $output;
        }

        $output['error'] = [
            'message' => $data
        ];

        if ($this->isWithTrace()) {
            $output['error']['trace'] = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
        }

        return $output;
    }

    /**
     * @return $this
     */
    public function useTrace() : ResponseStandard
    {
        $this->withTrace = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function noTrace() : ResponseStandard
    {
        $this->withTrace = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithTrace() : bool
    {
        return $this->withTrace;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception|mixed $message
     * @param string|ResponseGeneratorAbstract|null $generator
     * @return ResponseStandard
     */
    public static function with(
        RequestInterface $request,
        ResponseInterface $response,
        $message,
        $generator = null
    ) : ResponseStandard {

        $object = new static($request, $response, $generator);
        $object->output = $message;
        return $object;
    }

    /**
     * @return ResponseInterface
     */
    public function serve() : ResponseInterface
    {
        return call_user_func_array(
            [
                $this->generator->setData($this->generateData($this->output)),
                'serve'
            ],
            func_get_args()
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception|mixed $message
     * @param string|ResponseGeneratorAbstract|null $generator
     * @return ResponseInterface
     */
    public static function withData(
        RequestInterface $request,
        ResponseInterface $response,
        $message,
        $generator = null
    ) : ResponseInterface {
        // arguments
        $args = func_get_args();
        // splice after next arguments
        array_splice($args, 4);
        return call_user_func_array(
            [
                static::with(
                    $request,
                    $response,
                    $message,
                    $generator
                ),
                'serve'
            ],
            $args
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception $exception
     * @param string|ResponseGeneratorAbstract|null $generator
     * @return ResponseInterface
     */
    public static function withException(
        RequestInterface $request,
        ResponseInterface $response,
        Exception $exception,
        $generator = null
    ) : ResponseInterface {
        $args = func_get_args();
        array_splice($args, 4);
        return call_user_func_array(
            [
                static::with(
                    $request,
                    $response,
                    $exception,
                    $generator
                ),
                'serve'
            ],
            $args
        );
    }

    /**
     * @return ResponseGeneratorAbstract|Html|Json|Text|Xml
     */
    public function getGenerator()
    {
        return $this->generator;
    }
}

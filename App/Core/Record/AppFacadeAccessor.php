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

use PentagonalProject\App\Rest\Abstracts\ContainerAccessor;
use PentagonalProject\App\Rest\Util\Sanitizer;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Http\Environment;

/**
 * Class AppFacadeAccessor
 * @package PentagonalProject\App\Rest\Record
 */
class AppFacadeAccessor extends ContainerAccessor
{
    /**
     * @var AppFacade
     */
    protected $facade;

    /**
     * @var App
     */
    protected $slim;

    /**
     * @var string
     */
    protected $webRootPath;

    /**
     * AppFacadeAccessor constructor.
     * @param AppFacade $facade
     */
    public function __construct(AppFacade &$facade)
    {
        $this->facade = $facade;
    }

    /**
     * @param ContainerInterface $container
     * @return AppFacadeAccessor
     */
    public function create(ContainerInterface $container) : AppFacadeAccessor
    {
        $this->slim = new App($container);
        $this->setContainerAggregate($this->slim->getContainer());
        return $this;
    }

    /**
     * Substantial & Inheritance Looping Procedure
     *
     * @return AppFacade
     */
    public function getFacade() : AppFacade
    {
        return $this->facade;
    }

    /**
     * Get Slim Application
     *
     * @return App
     */
    public function getApp() : App
    {
        return $this->slim;
    }

    /**
     * Get WebRoot Path
     *
     * @return string
     */
    public function getWebRootPath()
    {
        if (!isset($this->webRootPath)) {
            /**
             * @var Environment $environment
             */
            $environment = $this->getContainer()->get('environment');
            return Sanitizer::fixDirectorySeparator(
                dirname(
                    isset($environment['SCRIPT_FILENAME'])
                        ? $environment['SCRIPT_FILENAME']
                        : $_SERVER['SCRIPT_FILENAME']
                )
            );
        }

        return $this->webRootPath;
    }
}

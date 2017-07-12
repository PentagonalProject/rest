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

namespace PentagonalProject\Modules\Recipicious\Task;

use Apatis\ArrayStorage\Collection;
use PentagonalProject\App\Rest\Abstracts\ResponseGeneratorAbstract;
use PentagonalProject\App\Rest\Generator\Response\Json;
use PentagonalProject\App\Rest\Generator\Response\Xml;
use PentagonalProject\App\Rest\Record\ModularCollection;
use PentagonalProject\Modules\Recipicious\Recipicious;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * Class MainWorker
 * @package PentagonalProject\Modules\Recipicious\Tasks
 */
class MainWorker
{
    /**
     * @var Recipicious
     */
    protected $module;

    /**
     * @var bool
     */
    protected $hasRun = false;

    /**
     * MainWorker constructor.
     * @param Recipicious $module
     */
    public function __construct(Recipicious &$module)
    {
        $this->module = $module;
    }

    /**
     * @return MainWorker
     * @see MainWorker::sendRoutes() to get Routes collection set
     * @todo Just Info
     */
    public function run()
    {
        if ($this->hasRun) {
            return $this;
        }
        $this->hasRun = true;

        return $this->sendRoutes();
    }

    /**
     * @return MainWorker
     */
    private function sendRoutes()
    {
        /**
         * Instance Slim as Container App
         * or uses ->
         *          AppFacade::current()->getAccessor()->getApp()
         *
         * @var App $slim
         */
        $slim = $this->module->getContainer()['app'];
        $class =& $this;
        /*
         * JUST ACCESS WITH:
         * http://target/?output=xml -> to get XML data
         */
        $slim->any(
            '{param: .+}',
            function (ServerRequestInterface $request, ResponseInterface $response) use ($class) {
                /**
                 * @var ContainerInterface $this
                 * @var ModularCollection $module
                 */
                $module = $this->module;

                /* -------------------------------------------------------
                 * INFO
                 * ------------------------------------------------------ */
                /**
                 * Modular Collection Info
                 */
                $collection = $module->getModularInformation($class->module->getModularNameSelector());
                /**
                 * Get From @uses Collection
                 */
                $info = $collection->all();
                // or use below to get module info
                $info = $class->module->getModularInfo();

                /* -------------------------------------------------------
                 * BUILT IN JSON RESPONSE
                 * ------------------------------------------------------ */
                $responseBuilderClass = Json::class;
                // if get param output == xml
                if (isset($_GET['output']) && $_GET['output'] == 'xml') {
                    // override ResponseGeneratorAbstract to Xml instance
                    $responseBuilderClass = Xml::class;
                }

                /**
                 * @uses Json|XML to generate Data JSON and Server it
                 * @var ResponseGeneratorAbstract $responseBuilderClass
                 */
                $responseBuilder = $responseBuilderClass::generate($request, $response);
                /**
                 * set Data into @uses ResponseGeneratorAbstract
                 */
                $responseBuilder->setData(["Module" => $info]);
                /**
                 * Serve / Build The Response
                 * @var ResponseInterface $response
                 */
                $response = $responseBuilder->serve();
                return $response;
            }
        );

        return $this;
    }
}

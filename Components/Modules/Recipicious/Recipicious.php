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

namespace PentagonalProject\Modules\Recipicious;

use PentagonalProject\App\Rest\Abstracts\ModularAbstract;
use PentagonalProject\App\Rest\Util\ComposerLoaderPSR4;
use PentagonalProject\Modules\Recipicious\Lib\Api;
use PentagonalProject\Modules\Recipicious\Task\MainWorker;

/**
 * Class Recipicious
 * @package PentagonalProject\Modules\Recipicious
 */
class Recipicious extends ModularAbstract
{
    /**
     * @var string
     */
    protected $modular_name = 'Recipicious';

    /**
     * @var string
     */
    protected $modular_description = 'Recipicious Module for Recipes!';

    /**
     * @var string
     */
    protected $modular_uri = 'https://www.pentagonal.org';

    /**
     * @var string
     */
    protected $modular_author = 'Pentagonal Development';

    /**
     * @var string
     */
    protected $modular_version = '1.0.0';

    /**
     * @var MainWorker
     */
    protected $worker;

    /**
     * @const string
     */
    const PATTERN = '/recipes';

    /**
     * {@inheritdoc}
     * @see MainWorker::run()
     */
    public function init()
    {
        // register AutoLoader
        // and run it
        ComposerLoaderPSR4::create([
            __NAMESPACE__ . '\\Lib\\'   => __DIR__ . '/Libs/',
            __NAMESPACE__ . '\\Model\\' => __DIR__ . '/Models/',
            __NAMESPACE__ . '\\Task\\'  => __DIR__ . '/Tasks/'
        ])->register();
        $this->worker = new MainWorker(new Api($this));
        $this->worker->run();
    }

    /**
     * @return string
     */
    public function getGroupPattern() : string
    {
        return self::PATTERN;
    }

    /**
     * @return MainWorker
     */
    public function getWorker() : MainWorker
    {
        return $this->worker;
    }
}

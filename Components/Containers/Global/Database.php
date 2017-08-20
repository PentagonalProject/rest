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

    use Illuminate\Database\Capsule\Manager;
    use PentagonalProject\App\Rest\Record\Configurator;
    use PentagonalProject\App\Rest\Record\AppFacade;
    use Psr\Container\ContainerInterface;
    use Illuminate\Events\Dispatcher;
    use Illuminate\Container\Container;

    /**
     * @param ContainerInterface $container
     * @return Manager
     */
    return function (ContainerInterface $container) : Manager {
        $capsule = new Manager();
        /**
         * @var Configurator $config
         */
        $config = $container['config'];
        $database = $config->get('database', []);
        $database['options'] = isset($database['options'])
            && is_array($database['options'])
            ? $database['options']
            : [];
        $database['options'][PDO::ATTR_CASE] = PDO::CASE_LOWER;
        $config->set('database', $database);

        $capsule->addConnection(
            $database,
            AppFacade::current()->getName()
        );

        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        return $capsule;
    };

}

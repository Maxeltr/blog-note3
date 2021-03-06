<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmFile\Hydrator\FileMapperHydrator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use MxmFile\Hydrator\Strategy\DateTimeFormatterStrategy;
use MxmFile\Hydrator\Strategy\OwnerStrategy;
use MxmFile\Hydrator\Strategy\ClientStrategy;

class FileMapperHydratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fileHydrator = new ReflectionHydrator();
        $fileHydrator->setNamingStrategy(new UnderscoreNamingStrategy());
        $fileHydrator->addStrategy('uploadDate', $container->get(DateTimeFormatterStrategy::class));
        $fileHydrator->addStrategy('changeDate', $container->get(DateTimeFormatterStrategy::class));
        $fileHydrator->addStrategy('owner', $container->get(OwnerStrategy::class));
        $fileHydrator->addStrategy('client', $container->get(ClientStrategy::class));

        return $fileHydrator;
    }
}
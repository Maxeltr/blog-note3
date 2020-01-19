<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

namespace MxmUser\Factory\Hydrator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\Aggregate\AggregateHydrator;
use MxmUser\Hydrator\UserMapperHydrator\UserHydrator;
use MxmUser\Hydrator\UserMapperHydrator\DatesHydrator;
use MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator;
use Laminas\Config\Config;
use MxmUser\Date;

class UserMapperHydratorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = new Config($container->get('config'));

        $userHydrator = new UserHydrator();

        $datetime = $container->get('datetime');
        $dateValidator = $container->get(Date::class);
        $datesHydrator = new DatesHydrator($datetime, $dateValidator, $config);

        $timebeltHydrator = new TimebeltHydrator($config);

        $aggregatehydrator = new AggregateHydrator();
        $aggregatehydrator->setEventManager($container->get('EventManager'));
        $aggregatehydrator->add($userHydrator);
        $aggregatehydrator->add($datesHydrator);
        $aggregatehydrator->add($timebeltHydrator);

        return $aggregatehydrator;
    }
}
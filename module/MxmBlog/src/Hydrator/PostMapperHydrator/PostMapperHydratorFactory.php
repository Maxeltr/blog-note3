<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmBlog\Hydrator\PostMapperHydrator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use MxmDateTime\Strategy\DateTimeImmutableFormatterStrategy;
use MxmUser\Hydrator\Strategy\UserStrategy;
use MxmBlog\Hydrator\Strategy\CategoryStrategy;
use Laminas\Hydrator\NamingStrategy\MapNamingStrategy;
use Laminas\Hydrator\Aggregate\AggregateHydrator;
use MxmBlog\Model\TagRepositoryInterface;

class PostMapperHydratorFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $hydrator = new ReflectionHydrator();
//        $hydrator->setNamingStrategy(new UnderscoreNamingStrategy());
        $hydrator->addStrategy('created', $container->get(DateTimeImmutableFormatterStrategy::class));
        $hydrator->addStrategy('updated', $container->get(DateTimeImmutableFormatterStrategy::class));
        $hydrator->addStrategy('published', $container->get(DateTimeImmutableFormatterStrategy::class));
        $namingStrategy = MapNamingStrategy::createFromHydrationMap([
                    'categoryId' => 'category'
        ]);
        $hydrator->setNamingStrategy($namingStrategy);
        $hydrator->addStrategy('category', $container->get(CategoryStrategy::class));
        $hydrator->addStrategy('author', $container->get(UserStrategy::class));

        $aggregatehydrator = new AggregateHydrator();
        $aggregatehydrator->setEventManager($container->get('EventManager'));
        $aggregatehydrator->add($hydrator);

        $tagRepository = $container->get(TagRepositoryInterface::class);
        $tagsHydrator = new TagCloudHydrator($tagRepository);
        $aggregatehydrator->add($tagsHydrator);

        return $aggregatehydrator;
    }

}

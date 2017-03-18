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

namespace MxmBlog\Factory\Mapper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Config\Config;
use Zend\Db\Adapter\Adapter;
use MxmBlog\AggregateHydrator;
use MxmBlog\Mapper\ZendDbSqlMapper;
use MxmBlog\Hydrator\Tag\TagHydrator;
use MxmBlog\Model\CategoryInterface;
use MxmBlog\Model\TagInterface;
use MxmBlog\Model\PostInterface;

class ZendDbSqlMapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $aggregatehydrator = $container->get(AggregateHydrator::class);
                
        $classMethodsHydrator = new ClassMethods(false);
        $tagHydrator = $container->get(TagHydrator::class);
        
        $category = $container->get(CategoryInterface::class);
        $tag = $container->get(TagInterface::class);
        $post = $container->get(PostInterface::class);
        
        $config = new Config($container->get('config'));
        
        $adapter = $container->get(Adapter::class);
        
        return new ZendDbSqlMapper(
            $adapter,
            $aggregatehydrator,
            $tagHydrator,
            $classMethodsHydrator,
            $post,
            $category,
            $tag,
            $config->blog_module
        );
    }
}
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

namespace MxmBlog\Factory\Controller;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;
use MxmBlog\Date;
use Zend\ServiceManager\Factory\FactoryInterface;
use MxmBlog\Controller\ListController;
use MxmBlog\Service\PostServiceInterface;
use MxmBlog\Service\DateTimeInterface;
use Zend\Validator\NotEmpty;
use MxmBlog\Logger;
use MxmUser\Service\UserServiceInterface;

class ListControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $logger = $container->get(Logger::class);
        $config = new Config($container->get('config'));
        $postService = $container->get(PostServiceInterface::class);
        $dateValidator = $container->get(Date::class);
        $dateValidator->setFormat($config->blog_module->dateTime->dateTimeFormat);
        $datetime = $container->get(DateTimeInterface::class);
        $notEmptyValidator = new NotEmpty();
        $notEmptyValidator->setType(NotEmpty::ALL);
        $userService = $container->get(UserServiceInterface::class);

        return new ListController($postService, $dateValidator, $datetime, $config->blog_module, $notEmptyValidator, $logger, $userService);
    }
}
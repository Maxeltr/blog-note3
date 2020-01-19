<?php

/*
 * The MIT License
 *
 * Copyright 2016 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmBlog\Factory\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use MxmBlog\Mapper\MapperInterface;
use MxmBlog\View\Helper\ArchiveDates;
use MxmBlog\Date;
use Laminas\Config\Config;
use Laminas\Authentication\AuthenticationService;
use MxmUser\Model\UserInterface;

class ArchiveDatesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mapper = $container->get(MapperInterface::class);
        $dateValidator = $container->get(Date::class);
        $config = new Config($container->get('config'));
        $authenticationService = $container->get(AuthenticationService::class);

        $locale = null;

        if ($authenticationService->hasIdentity()) {
            $user = $authenticationService->getIdentity();
            $locale = $user->getLocale();
        }

        if (empty($locale)) {
            $sessionContainer = $container->get('MxmUserSessionContainer');

            if (isset($sessionContainer->locale)) {
                $locale = $sessionContainer->locale;
            } else {
                $locale = $config->defaults->locale;
            }
        }

        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL
        );

        return new ArchiveDates($mapper, $dateValidator, $formatter);
    }
}
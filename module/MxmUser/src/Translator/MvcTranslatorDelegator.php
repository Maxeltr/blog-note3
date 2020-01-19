<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmUser\Translator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\I18n\Translator\Resources;
use Laminas\Config\Config;
use Laminas\Authentication\AuthenticationService;

class MvcTranslatorDelegator implements DelegatorFactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $mvcTranslator = $callback();
        $translator = $mvcTranslator->getTranslator();

        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForValidator()
        );
        $translator->addTranslationFilePattern(
            'phpArray',
            Resources::getBasePath(),
            Resources::getPatternForCaptcha()
        );

        $authenticationService = $container->get(AuthenticationService::class);

        if ($authenticationService->hasIdentity()) {
            $user = $authenticationService->getIdentity();
            //if ($user instanceof \MxmUser\Model\UserInterface) {
                $locale = $user->getLocale();

                if (!empty($locale)) {
                    $translator->setLocale($locale);

                    return $mvcTranslator;
                }
            //}
        }

        $sessionContainer = $container->get('MxmUserSessionContainer');

        if (isset($sessionContainer->locale)) {
            $translator->setLocale($sessionContainer->locale);
        } else {
            $config = new Config($container->get('config'));
            $translator->setLocale($config->defaults->locale);
        }

        return $mvcTranslator;
    }
}
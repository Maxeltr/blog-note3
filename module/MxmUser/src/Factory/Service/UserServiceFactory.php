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

namespace MxmUser\Factory\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use MxmUser\Mapper\MapperInterface;
use MxmUser\Service\UserService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Crypt\Password\Bcrypt;
use MxmRbac\Service\AuthorizationService;
use MxmMail\Service\MailService;
use Zend\i18n\Translator\TranslatorInterface;
use MxmUser\Validator\IsPropertyMatchesDb;
use Laminas\Http\PhpEnvironment\Request;

class UserServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $mapper = $container->get(MapperInterface::class);
        $authorizationService = $container->get(AuthorizationService::class);
        $dateTime = $container->get('datetime');
        $authService = $container->get(AuthenticationService::class);
        $emailValidator = new EmailAddress();
        $notEmptyValidator = new NotEmpty();
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $recordExistsValidator = new RecordExists([
            'table'   => 'users',
            'field'   => 'email',
            'adapter' => $dbAdapter,
        ]);
        $isRoleMatchesDbvalidator = new IsPropertyMatchesDb([
            'table'   => 'users',
            'field'   => 'id',
            'adapter' => $dbAdapter,
            'property' => 'role'
        ]);
        $bcrypt = new Bcrypt();
        $mail = $container->get(MailService::class);
        $sessionContainer = $container->get('MxmUserSessionContainer');
        $translator = $container->get(TranslatorInterface::class);
        $request = new Request();

        return new UserService(
            $mapper,
            $dateTime,
            $authService,
            $emailValidator,
            $notEmptyValidator,
            $recordExistsValidator,
            $isRoleMatchesDbvalidator,
            $authorizationService,
            $bcrypt,
            $mail,
            $sessionContainer,
            $translator,
            $request
        );
    }
}
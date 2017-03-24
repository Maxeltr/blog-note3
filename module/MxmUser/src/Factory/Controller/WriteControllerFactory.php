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

namespace MxmUser\Factory\Controller;

use MxmUser\Controller\WriteController;
use MxmUser\Form\EditUserForm;
use MxmUser\Form\RegisterUserForm;
use MxmUser\Form\ChangePasswordForm;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use MxmUser\Service\UserServiceInterface;
use MxmUser\Form\LoginUserForm;
use MxmUser\Form\ChangeEmailForm;

class WriteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $userService = $container->get(UserServiceInterface::class);

        $formManager = $container->get('FormElementManager');
        $editUserForm = $formManager->get(EditUserForm::class);
        $registerUserForm = $formManager->get(RegisterUserForm::class);
        $changePasswordForm = $formManager->get(ChangePasswordForm::class);
        $loginUserForm = $formManager->get(LoginUserForm::class);
        $changeEmailForm = $formManager->get(ChangeEmailForm::class);
        
        return new WriteController(
            $userService,
            $editUserForm,
            $registerUserForm,
            $changePasswordForm,
            $loginUserForm,
            $changeEmailForm
        );
    }
}
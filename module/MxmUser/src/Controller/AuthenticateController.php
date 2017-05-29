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

namespace MxmUser\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Form\FormInterface;
use Zend\Router\RouteInterface;
use Zend\Log\Logger;
use Zend\Config\Config;
use MxmUser\Service\UserServiceInterface;

class AuthenticateController extends AbstractActionController
{
    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \MxmUser\Service\UserServiceInterface
     */
    protected $userService;

    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Zend\Router\RouteInterface
     */
    protected $router;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     *
     * @var Zend\Form\FormInterface
     */
    protected $loginUserForm;

    public function __construct(
        UserServiceInterface $userService,
        FormInterface $loginUserForm,
        RouteInterface $router,
        Logger $logger,
        Config $config
    ) {
        $this->userService = $userService;
        $this->loginUserForm = $loginUserForm;
        $this->router = $router;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function loginUserAction()
    {
        $request = $this->getRequest();
        $loginError = false;

        if ($request->isPost()) {
            $this->loginUserForm->setData($request->getPost());
            if ($this->loginUserForm->isValid()) {
                $data = $this->loginUserForm->getData();
                try {
                    $result = $this->userService->loginUser($data['email'], $data['password']);
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                $resultCode = $result->getCode();
                if ($resultCode === Result::SUCCESS) {
                    if (!$this->isRouteExists($data['redirect'])) {     //TODO не работает. Добавить доп параметры для разных роутов

                        return $this->redirect()->toRoute('home');
                    } else {
                        $redirectUrl = $this->router->assemble([], ['name' => $data['redirect']]);
                        $this->redirect()->toUrl($redirectUrl);
                    }
                } elseif ($resultCode === Result::FAILURE_IDENTITY_NOT_FOUND) {
                    $loginError = 'Incorrect login.';
                } else {
                    $loginError = 'Incorrect login and/or password.';
                }
            }
        }
        $this->loginUserForm->get('redirect')->setValue($this->getRedirectRouteFromQuery());    //TODO не работает. Добавить доп параметры для разных роутов

        return new ViewModel([
            'form' => $this->loginUserForm,
            'error' => $loginError
        ]);
    }

    public function logoutUserAction()
    {
        try {
            $this->userService->logoutUser();
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        return $this->redirect()->toRoute('loginUser');
    }

    /**
     * @param $route
     * @return bool
     */
    private function isRouteExists($route)
    {
        if (empty($route)) {
            return false;
        }
                
        try {
            $this->router->assemble(array(), array('name' => $route));
        } catch (\Exception $e) {
            $this->logger->err('isRouteExists ' . $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());    //TODO Автоматически получать имя метода?

            return false;
        }

        return true;
    }

    /**
     * Проверяет параметр 'redirect' в GET. Возвращает путь на который перенаправить юзера.
     *
     * @return string
     */
    private function getRedirectRouteFromQuery()
    {
        $redirect = $this->params()->fromQuery('redirect', '');     //TODO не работает. Добавить доп параметры для разных роутов
        if ($redirect && $this->isRouteExists($redirect)) {

            return $redirect;
        }

        return false;
    }
}

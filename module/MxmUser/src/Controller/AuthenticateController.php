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
use Zend\Http\Request;
use MxmUser\Exception\ExpiredUserException;

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
        $loginError = '';

        if ($request->isPost()) {
            $this->loginUserForm->setData($request->getPost());
            if ($this->loginUserForm->isValid()) {
                $data = $this->loginUserForm->getData();
                try {
                    $result = $this->userService->loginUser($data['email'], $data['password']);
                } catch (ExpiredUserException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();		//TODO redirect to page with error
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                $resultCode = $result->getCode();
                if ($resultCode === Result::SUCCESS) {
                    $url = new Request();
                    $url->setMethod(Request::METHOD_GET);
                    try {
                        $url->setUri($data['redirect']);
                    } catch (\Exception $e) {
                        return $this->redirect()->toRoute('home');
                    }
                    $routeMatch = $this->router->match($url);
                    if ($routeMatch === null) {

                        return $this->redirect()->toRoute('home');
                    } else {

                        return $this->redirect()->toRoute($routeMatch->getMatchedRouteName(), $routeMatch->getParams());
                    }
                } elseif ($resultCode === Result::FAILURE_IDENTITY_NOT_FOUND) {
                    $loginError = 'Incorrect login.';
                } else {
                    $loginError = 'Incorrect login and/or password.';
                }
            }
        }

        $redirect = new Request();
        $redirect->setMethod(Request::METHOD_GET);
        $redirect->setUri($this->params()->fromQuery('redirect', $this->url()->fromRoute('home')));

	if ($this->router->match($redirect) !== null) {
            $this->loginUserForm->get('redirect')->setValue($redirect->getUriString());
	} else {
            $this->loginUserForm->get('redirect')->setValue($this->url()->fromRoute('home'));
	}

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
}

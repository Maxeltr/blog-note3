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
use MxmUser\Service\UserServiceInterface;
use MxmUser\Exception\RuntimeException;
use MxmUser\Exception\AlreadyExistsUserException;
use Zend\Form\FormInterface;
use Zend\Router\RouteInterface;
use Zend\Authentication\Result;
use Zend\Log\Logger;

class WriteController extends AbstractActionController
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
     *
     * @var Zend\Form\FormInterface 
     */
    protected $editUserForm;
    protected $registerUserForm;
    protected $changePasswordForm;
    protected $loginUserForm;
    protected $changeEmailForm;
        
    public function __construct(
        Logger $logger,
        UserServiceInterface $userService,
        FormInterface $editUserForm,
        FormInterface $registerUserForm,
        FormInterface $changePasswordForm,
        FormInterface $loginUserForm,
        FormInterface $changeEmailForm,
        RouteInterface $router
    ) {
        $this->logger = $logger;
        $this->userService = $userService;
        $this->editUserForm = $editUserForm;
        $this->registerUserForm = $registerUserForm;
        $this->changePasswordForm = $changePasswordForm;
        $this->loginUserForm = $loginUserForm;
        $this->changeEmailForm = $changeEmailForm;
        $this->router = $router;
    }
    
    public function LoginUserAction()
    {
        $request = $this->getRequest();
        $redirectUrl = $this->getRedirectRouteFromQuery();
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
                    $redirectUrl = $this->getRedirectRouteFromPost();
                    if(empty($redirectUrl)) {
                        return $this->redirect()->toRoute('home');
                    } else {
                        $this->redirect()->toUrl($redirectUrl);
                    }
                } elseif ($resultCode === Result::FAILURE_IDENTITY_NOT_FOUND) {
                    $loginError = 'Incorrect login.';
                } else {
                    $loginError = 'Incorrect login and/or password.';
                }
            }
        }

        return new ViewModel(array(
            'form' => $this->loginUserForm,
            'redirect' => $redirectUrl,
            'error' => $loginError
        ));
    }

    public function LogoutUserAction() 
    {
        try {
            $this->userService->logoutUser();
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }
        
        return $this->redirect()->toRoute('login');
    }
    
    public function AddUserAction()
    {
        $request = $this->getRequest();
        $registerError = false;
        
        if ($request->isPost()) {
            $this->registerUserForm->setData($request->getPost());
            if ($this->registerUserForm->isValid()) {
                try {
                    $savedUser = $this->userService->insertUser($this->registerUserForm->getData());
                } catch (AlreadyExistsUserException $e) {
                    $registerError = $e->getMessage();
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $savedUser->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $this->registerUserForm,
            'error' => $registerError ? $registerError : false
        ));
    }
    
    public function ChangeEmailAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->changeEmailForm->setData($request->getPost());
            if ($this->changeEmailForm->isValid()) {
                try {
                    $this->userService->changeEmail($this->changeEmailForm->getData());
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $savedUser->getId()));     //TODO получить id текущего юзера добавить flashmessenger
            }
        }

        return new ViewModel(array(
            'form' => $this->changeEmailForm
        ));
    }
    
    public function EditUserAction()
    {
        $request = $this->getRequest();
        try {
            $user = $this->userService->findUserById($this->params('id'));
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }
        
        $this->editUserForm->bind($user);   //связываем форму и объект
        if ($request->isPost()) {
            $this->editUserForm->setData($request->getPost());  //данные устанавливаются и в форму и в объект, т.к. форма и объект связаны
            if ($this->editUserForm->isValid()) {
                try {
                    $this->userService->updateUser($user);
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $user->getId()));
            }
        }
 
        return new ViewModel(array(
                'form' => $this->editUserForm
        ));
    }
    
    public function ChangePasswordAction()
    {
        //TODO проверить авторизацию если нет то перенаправить
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $this->changePasswordForm->setData($request->getPost());
            if ($this->changePasswordForm->isValid()) {
                try {
                    $this->userService->changePassword($this->changePasswordForm->getData());
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $user->getId()));     //TODO получить id текущего юзера добавить flashmessenger
            }
        }
 
        return new ViewModel(array(
                'form' => $this->changePasswordForm
        ));
    }
    
    public function ResetPasswordAction()
    {
        return new ViewModel([
            'message' => 'ResetPasswordAction'
        ]);
    }
    
    /**
     * Проверяет параметр 'redirect' в GET. Возвращает путь на который перенаправить юзера.
     *
     * @return string
     */
    private function getRedirectRouteFromQuery()
    {
        $redirect = $this->params()->fromQuery('redirect', '');
        if ($redirect && $this->routeExists($redirect)) {
            return $redirect;
        }

        return false;
    }
    
    /**
     * Проверяет параметр 'redirect' в POST. Возвращает путь на который перенаправить юзера.
     *
     * @return string
     */
    private function getRedirectRouteFromPost()
    {
        $redirect = $this->params()->fromPost('redirect', '');
        if ($redirect && $this->routeExists($redirect)) {
            return $redirect;
        }

        return false;
    }

    /**
     * @param $route
     * @return bool
     */
    private function routeExists($route)
    {
        try {
            $this->router->assemble(array(), array('name' => $route));
        } catch (Exception\RuntimeException $e) {
            return false;
        }
        return true;
    }
}
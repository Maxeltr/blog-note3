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
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
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
                    if (!$this->isRouteExists($data['redirect'])) {

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
        $this->loginUserForm->get('redirect')->setValue($this->getRedirectRouteFromQuery());

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

    public function addUserAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->registerUserForm->setData($request->getPost());
            if ($this->registerUserForm->isValid()) {
                try {
                    $savedUser = $this->userService->insertUser($this->registerUserForm->getData());
                } catch (AlreadyExistsUserException $e) {

                    return new ViewModel([
                        'form' => $this->registerUserForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailUser',    //TODO автоматически логинить юзера или перенаправить на страницу login?
                    ['id' => $savedUser->getId()]
                );
            }
        }

        return new ViewModel([
            'form' => $this->registerUserForm,
        ]);
    }

    public function changeEmailAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->changeEmailForm->setData($request->getPost());
            if ($this->changeEmailForm->isValid()) {
                $data = $this->changeEmailForm->getData();
                try {
                    $user = $this->userService->changeEmail($data['newEmail'], $data['password']);
                } catch (InvalidPasswordUserException $e) {

                    return new ViewModel([
                        'form' => $this->changeEmailForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (NotAuthenticatedUserException $e) {

                    return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => 'changeEmail']]); //TODO использовать flashmessenger?
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailUser',
                    array('id' => $user->getId()));     //TODO добавить flashmessenger
            }
        }

        return new ViewModel(array(
            'form' => $this->changeEmailForm
        ));
    }

    public function editUserAction()
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
                    ['id' => $user->getId()]);
            }
        }

        return new ViewModel([
            'form' => $this->editUserForm
        ]);
    }

    public function changePasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->changePasswordForm->setData($request->getPost());
            if ($this->changePasswordForm->isValid()) {
                $data = $this->changePasswordForm->getData();
                try {
                    $user = $this->userService->changePassword($data['oldPassword'], $data['newPassword']);
                } catch (InvalidPasswordUserException $e) {

                    return new ViewModel([
                        'form' => $this->changePasswordForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (NotAuthenticatedUserException $e) {

                    return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => 'changePassword']]); //TODO использовать flashmessenger?
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailUser',
                    ['id' => $user->getId()]);     //TODO добавить flashmessenger
            }
        }

        return new ViewModel([
                'form' => $this->changePasswordForm
        ]);
    }

    public function resetPasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->resetPasswordForm->setData($request->getPost());
            if ($this->resetPasswordForm->isValid()) {
                $data = $this->resetPasswordForm->getData();
                try {
                    $result = $this->userService->resetPassword($data['email']);
                } catch (RecordNotFoundUserException $e) {
                    
                    return new ViewModel([
                        'form' => $this->resetPasswordForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

            }
        }

        return new ViewModel([
            'form' => $this->resetPasswordForm
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
        if ($redirect && $this->isRouteExists($redirect)) {

            return $redirect;
        }

        return false;
    }

    /**
     * @param $route
     * @return bool
     */
    private function isRouteExists($route)
    {
        try {
            $this->router->assemble(array(), array('name' => $route));
        } catch (\Zend\Router\Exception\RuntimeException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return false;
        }

        return true;
    }
}
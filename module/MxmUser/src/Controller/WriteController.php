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
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use Zend\Form\FormInterface;
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
    protected $editPasswordForm;
    protected $editEmailForm;
    protected $resetPasswordForm;
    protected $setPasswordForm;

    public function __construct(
        Logger $logger,
        UserServiceInterface $userService,
        FormInterface $editUserForm,
        FormInterface $registerUserForm,
        FormInterface $editPasswordForm,
        FormInterface $editEmailForm,
        FormInterface $resetPasswordForm,
        FormInterface $setPasswordForm
    ) {
        $this->logger = $logger;
        $this->userService = $userService;
        $this->editUserForm = $editUserForm;
        $this->registerUserForm = $registerUserForm;
        $this->editPasswordForm = $editPasswordForm;
        $this->editEmailForm = $editEmailForm;
        $this->resetPasswordForm = $resetPasswordForm;
        $this->setPasswordForm = $setPasswordForm;
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

    public function editEmailAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->editEmailForm->setData($request->getPost());
            if ($this->editEmailForm->isValid()) {
                $data = $this->editEmailForm->getData();
                try {
                    $user = $this->userService->editEmail($data['newEmail'], $data['password']);
                } catch (InvalidPasswordUserException $e) {

                    return new ViewModel([
                        'form' => $this->editEmailForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (NotAuthenticatedUserException $e) {

                    return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => 'editEmail']]); //TODO использовать flashmessenger?
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailUser',
                    array('id' => $user->getId()));     //TODO добавить flashmessenger
            }
        }

        return new ViewModel(array(
            'form' => $this->editEmailForm
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

    public function editPasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->editPasswordForm->setData($request->getPost());
            if ($this->editPasswordForm->isValid()) {
                $data = $this->editPasswordForm->getData();
                try {
                    $user = $this->userService->editPassword($data['oldPassword'], $data['newPassword']);
                } catch (InvalidPasswordUserException $e) {

                    return new ViewModel([
                        'form' => $this->editPasswordForm,
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                } catch (NotAuthenticatedUserException $e) {

                    return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => 'editPassword']]); //TODO использовать flashmessenger?
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailUser',
                    ['id' => $user->getId()]);     //TODO добавить flashmessenger
            }
        }

        return new ViewModel([
                'form' => $this->editPasswordForm
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
                    $model = new ViewModel([
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('home');  //TODO приделать flashmessenger с инструкциями?
            }
        }

        return new ViewModel([
            'form' => $this->resetPasswordForm
        ]);
    }

    public function setPasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->setPasswordForm->setData($request->getPost());
            if ($this->setPasswordForm->isValid()) {
                $data = $this->setPasswordForm->getData();
                try {
                    $result = $this->userService->setPassword($data['password'], $data['token']);
                } catch (RecordNotFoundUserException $e) {
                    $model = new ViewModel([
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                } catch (ExpiredUserException $e) {
                    $model = new ViewModel([
                        'error' => $e->getMessage()     //TODO использовать flashmessenger?
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('loginUser');  //TODO приделать flashmessenger с инструкциями?
            }
        }

        $token = $this->params()->fromRoute('token', null);
        $this->setPasswordForm->get('token')->setValue($token);

        return new ViewModel([
            'form' => $this->setPasswordForm
        ]);
    }
}
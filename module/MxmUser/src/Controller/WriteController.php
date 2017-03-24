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
use MxmUser\Exception\DataBaseErrorUserException;
use Zend\Form\FormInterface;

class WriteController extends AbstractActionController
{
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
        UserServiceInterface $userService,
        FormInterface $editUserForm,
        FormInterface $registerUserForm,
        FormInterface $changePasswordForm,
        FormInterface $loginUserForm,
        FormInterface $changeEmailForm
    ) {
        $this->userService = $userService;
        $this->editUserForm = $editUserForm;
        $this->registerUserForm = $registerUserForm;
        $this->changePasswordForm = $changePasswordForm;
        $this->loginUserForm = $loginUserForm;
        $this->changeEmailForm = $changeEmailForm;
    }
    
    public function LoginUserAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->loginUserForm->setData($request->getPost());
            if ($this->loginUserForm->isValid()) {
                try {
                    $this->userService->loginUser($this->loginUserForm->getData());
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $savedUser->getId()));     //TODO получить id текущего юзера добавить flashmessenger
            }
        }

        return new ViewModel(array(
            'form' => $this->loginUserForm
        ));
    }

    public function AddUserAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->registerUserForm->setData($request->getPost());
            if ($this->registerUserForm->isValid()) {
                try {
                    $savedUser = $this->userService->insertUser($this->registerUserForm->getData());
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('id' => $savedUser->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $this->registerUserForm
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
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
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
        } catch (DataBaseErrorUserException $e) {
            //TODO Записать в лог
            return $this->notFoundAction();
        }
        
        $this->editUserForm->bind($user);   //связываем форму и объект
        if ($request->isPost()) {
            $this->editUserForm->setData($request->getPost());  //данные устанавливаются и в форму и в объект, т.к. форма и объект связаны
            if ($this->editUserForm->isValid()) {
                try {
                    $this->userService->updateUser($user);
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
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
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
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
    
}
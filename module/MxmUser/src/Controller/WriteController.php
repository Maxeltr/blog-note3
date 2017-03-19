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
    protected $userForm;
    
    public function __construct(
        UserServiceInterface $userService, 
        FormInterface $userForm
    ) {
        $this->userService = $userService;
        $this->userForm = $userForm;
    }
    
    public function AddUserAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->userForm->setData($request->getPost());
            if ($this->userForm->isValid()) {
                try {
                    $savedUser = $this->userService->insertUser($this->userForm->getData());
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('action' => 'detail', 'id' => $savedUser->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $this->userForm
        ));
    }
    
    public function EditAction()
    {
        $request = $this->getRequest();
        try {
            $user = $this->userService->findUserById($this->params('id'));
        } catch (DataBaseErrorUserException $e) {
            //TODO Записать в лог
            return $this->notFoundAction();
        }
        
        $this->userForm->bind($user);
        if ($request->isPost()) {
            $this->userForm->setData($request->getPost());
            if ($this->userForm->isValid()) {
                try {
                    $this->userService->updateUser($user);
                } catch (DataBaseErrorUserException $e) {
                    //TODO Записать в лог
                    return $this->notFoundAction();
                }
                
                return $this->redirect()->toRoute('detailUser', 
                    array('action' => 'detail', 'id' => $user->getId()));
            }
        }
 
        return new ViewModel(array(
                'form' => $this->userForm
        ));
    }
    
    public function ChangePasswordAction()
    {
        return new ViewModel([
            'message' => 'ChangePasswordAction'
        ]);
    }
    
    public function ResetPasswordAction()
    {
        return new ViewModel([
            'message' => 'ResetPasswordAction'
        ]);
    }
}
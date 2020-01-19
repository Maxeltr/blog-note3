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

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use MxmUser\Service\UserServiceInterface;
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\NotAuthorizedUserException;
use Laminas\Form\FormInterface;
use Laminas\Log\Logger;
use Laminas\Http\Request;
use Laminas\Router\RouteInterface;
use Laminas\Session\Container;
use Zend\i18n\Translator\TranslatorInterface;
use Laminas\Db\Adapter\Exception\InvalidQueryException;

class WriteController extends AbstractActionController
{
    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    /**
     * @var \MxmUser\Service\UserServiceInterface
     */
    protected $userService;

    /**
     *
     * @var Laminas\Form\FormInterface
     */
    protected $editUserForm;
    protected $registerUserForm;
    protected $editPasswordForm;
    protected $editEmailForm;
    protected $resetPasswordForm;
    protected $setPasswordForm;
    protected $sessionContainer;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(
        Logger $logger,
        UserServiceInterface $userService,
        FormInterface $editUserForm,
        FormInterface $registerUserForm,
        FormInterface $editPasswordForm,
        FormInterface $editEmailForm,
        FormInterface $resetPasswordForm,
        FormInterface $setPasswordForm,
        Container $sessionContainer,
        TranslatorInterface $translator
    ) {
        $this->logger = $logger;
        $this->userService = $userService;
        $this->editUserForm = $editUserForm;
        $this->registerUserForm = $registerUserForm;
        $this->editPasswordForm = $editPasswordForm;
        $this->editEmailForm = $editEmailForm;
        $this->resetPasswordForm = $resetPasswordForm;
        $this->setPasswordForm = $setPasswordForm;
        $this->sessionContainer = $sessionContainer;
        $this->translator = $translator;
    }

    public function addUserAction()
    {
        $error = null;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->registerUserForm->setData($request->getPost());
            if ($this->registerUserForm->isValid()) {
                try {
                    $savedUser = $this->userService->insertUser($this->registerUserForm->getData());
                } catch (AlreadyExistsUserException $ex) {
                    $this->logger->err($ex->getFile() . ' ' . $ex->getLine() . ' ' . $ex->getMessage());

                    return new ViewModel([
                        'form' => $this->registerUserForm,
                        'error' => $this->translator->translate('User has registered alredy')
                    ]);
                } catch (\Laminas\Mail\Transport\Exception\RuntimeException $ex) {
                    $this->logger->err($ex->getFile() . ' ' . $ex->getLine() . ' ' . $ex->getMessage());

                    return new ViewModel([
                        'form' => $this->registerUserForm,
                        'error' => $this->translator->translate('An error occurred while sending the email'). '. '
                            . $this->translator->translate('Check that your email address is correct')
                    ]);
                }
                $this->flashMessenger()->addMessage($this->translator->translate('An email was sent to the email address you provided with instructions to confirm your registration'), 'info');

                return $this->redirect()->toRoute('detailUser',    //TODO автоматически логинить юзера или перенаправить на страницу login?
                    ['id' => $savedUser->getId()]
                );
            } else {
                $error = true;
            }
        }

        return new ViewModel([
            'form' => $this->registerUserForm,
            'error' => $error
        ]);
    }

    public function editEmailAction()
    {
        $error = null;
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
                        'error' => 'Invalid password.'
                    ]);
                } catch (InvalidQueryException $e) {

                    return new ViewModel([
                        'form' => $this->editEmailForm,
                        'error' => 'This email is already in use'
                    ]);
                }
 
                return $this->redirect()->toRoute('detailUser',
                    array('id' => $user->getId()));
            } else {
                $error = true;
            }
        }

        return new ViewModel([
            'form' => $this->editEmailForm,
            'error' => $error
        ]);
    }

    public function editUserAction()
    {
        $request = $this->getRequest();
        $user = $this->userService->findUserById($this->params('id'));

        $this->editUserForm->bind($user);   //связываем форму и объект
        if ($request->isPost()) {
            $this->editUserForm->setData($request->getPost());  //данные устанавливаются и в форму и в объект, т.к. форма и объект связаны
            if ($this->editUserForm->isValid()) {
                $this->userService->updateUser($user);

                return $this->redirect()->toRoute('detailUser',
                    ['id' => $user->getId()]
                );
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
                        'error' => 'Invalid password'
                    ]);
                }

                return $this->redirect()->toRoute('detailUser',
                    ['id' => $user->getId()]
                );
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
                    $this->userService->resetPassword($data['email']);
                } catch (RecordNotFoundUserException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot reset password. Email not found.'
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                }

                return $this->redirect()->toRoute('home');
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
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot change password. Token not found.'
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                } catch (ExpiredUserException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    $model = new ViewModel([
                        'errorMessage' => 'Cannot change password. The token has expired.'
                    ]);
                    $model->setTemplate('mxm-user/write/error');

                    return $model;
                }

                return $this->redirect()->toRoute('loginUser');
            }
        }

        $token = $this->params()->fromRoute('token', null);
        $this->setPasswordForm->get('token')->setValue($token);

        return new ViewModel([
            'form' => $this->setPasswordForm
        ]);
    }

    public function confirmEmailAction()
    {
        $token = $this->params()->fromRoute('token', null);
        try {
            $this->userService->confirmEmail($token);
        } catch (RecordNotFoundUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            $model = new ViewModel([
                'errorMessage' => 'Registration cannot be completed. Token not found.'
            ]);
            $model->setTemplate('mxm-user/write/error');

            return $model;
        } catch (ExpiredUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            $model = new ViewModel([
                'errorMessage' => 'Registration cannot be completed. The token has expired.'
            ]);
            $model->setTemplate('mxm-user/write/error');

            return $model;
        }

        return $this->redirect()->toRoute('loginUser');
    }

    public function changeLanguageAction()
    {
        $lang = $this->params()->fromQuery('lang', null);
        $this->userService->changeLanguage($lang);

        $redirect = $this->params()->fromQuery('redirect', null);

        $url = new Request();
        $url->setMethod(Request::METHOD_GET);
        try {
            $url->setUri($redirect);
	} catch (\Exception $e) {
            return $this->redirect()->toRoute('home');
	}

	$routeMatch = $this->router->match($url);
	if ($routeMatch === null) {
            return $this->redirect()->toRoute('home');
	}

	return $this->redirect()->toRoute($routeMatch->getMatchedRouteName(), $routeMatch->getParams());
    }
}
<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

use MxmUser\Service\UserServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use Zend\Log\Logger;
use MxmUser\Exception\NotAuthorizedUserException;
use Zend\i18n\Translator\TranslatorInterface;

class DeleteController extends AbstractActionController
{
    /**
     * @var \User\Service\UserServiceInterface
     */
    protected $userService;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(UserServiceInterface $userService, Logger $logger, TranslatorInterface $translator)
    {
        $this->userService = $userService;
	$this->logger = $logger;
        $this->translator = $translator;
    }

    public function deleteUserAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $user = $this->userService->findUserById($id);
        } catch (RecordNotFoundUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        } catch (NotAuthenticatedUserException $e) {
            $redirectUrl = $this->url()->fromRoute('detailUser', ['id' => $id]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (NotAuthorizedUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', $this->translator->translate('No'));

            if ($del === $this->translator->translate('Yes')) {
                $result = $this->userService->deleteUser($user);
                if ($result === false) {
                    $this->logger->err('DeleteController. User ' . $user->getID() . ' not deleted');

                    return $this->notFoundAction();	//выводить стр с ошибкой
                }
            }

            return $this->redirect()->toRoute('listUsers');	//TODO учитывать страницу, id и т.д.
        }

        return new ViewModel(array(
            'user' => $user
        ));
    }
}
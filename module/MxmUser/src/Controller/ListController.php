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
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Log\Logger;
use \DateTimeInterface;
use MxmUser\Service\UserServiceInterface;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\NotAuthorizedUserException;

class ListController extends AbstractActionController
{
    /**
     * @var \MxmUser\Service\UserServiceInterface
     */
    protected $userService;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(
        UserServiceInterface $userService,
        Config $config,
        Logger $logger
    ) {
        $this->userService = $userService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function listUsersAction()
    {
        try {
            $paginator = $this->userService->findAllUsers();
	} catch (NotAuthenticatedUserException $e) {
            $redirectUrl = $this->url()->fromRoute('listUsers', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]); //TODO использовать flashmessenger?
        } catch (NotAuthorizedUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();	//TODO redirect ot access denied
	} catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }
        $this->configurePaginator($paginator);

        return new ViewModel([
            'users' => $paginator,
            'route' => 'listUsers'
        ]);
    }

    public function detailUserAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $user = $this->userService->findUserById($id);
        } catch (RecordNotFoundUserException $e) {

            return $this->notFoundAction();
	} catch (NotAuthenticatedUserException $e) {
            $redirectUrl = $this->url()->fromRoute('detailUser', ['id' => $id]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]); //TODO использовать flashmessenger?
        } catch (NotAuthorizedUserException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();	//TODO redirect ot access denied
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'user' => $user
        ));
    }

    private function configurePaginator(Paginator $paginator)
    {
        $page = (int) $this->params()->fromRoute('page');
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->config->listController->ItemCountPerPage);

        return $this;
    }
}
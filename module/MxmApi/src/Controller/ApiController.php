<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmApi\Controller;

use Zend\Log\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use MxmApi\Service\ApiServiceInterface;
use Zend\Form\FormInterface;
use MxmApi\Exception\RuntimeException;
use MxmApi\Exception\ExpiredException;
use MxmApi\Exception\NotAuthenticatedException;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\RecordNotFoundException;
use MxmApi\Exception\AlreadyExistsException;
use MxmApi\Exception\InvalidPasswordException;
use MxmApi\Exception\NotAuthorizedException;
use MxmApi\Exception\DataBaseErrorException;

class ApiController extends AbstractActionController
{
    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     *
     * @var Zend\Form\FormInterface
     */
    protected $addClientForm;

    /**
     * @var \MxmApi\Service\ApiServiceInterface
     */
    protected $apiService;

    /**
     * @var Zend\Authentication\AuthenticationService;
     */
    protected $authService;

    public function __construct(
        ApiServiceInterface $apiService,
        FormInterface $addClientForm,
        //AuthenticationService $authService,
        Logger $logger
    ) {
        $this->apiService = $apiService;
        $this->addClientForm = $addClientForm;
        //$this->authService = $authService;
        $this->logger = $logger;
    }

    public function addClientAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->addClientForm->setData($request->getPost());
            if ($this->addClientForm->isValid()) {
                try {
                    $savedClient = $this->apiService->addClient($this->addClientForm->getData());
                } catch (AlreadyExistsException $e) {

                    return new ViewModel([
                        'form' => $this->addClientForm,
                        'error' => 'Client has registered alredy.'
                    ]);

                } catch (NotAuthenticatedException $e) {
                    $redirectUrl = $this->url()->fromRoute('addClient');

                    return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);

                } catch (NotAuthorizedException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');

                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailClient',    //TODO автоматически логинить юзера или перенаправить на страницу login?
                    ['client_id' => $savedClient['client_id']]
                );
            }
        }

        return new ViewModel([
            'form' => $this->addClientForm
        ]);
    }

	public function detailClientAction()
    {
        $id = $this->params()->fromRoute('client_id');
        try {
            $client = $this->apiService->findClientById($id);
        } catch (RecordNotFoundException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();

    	} catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('detailClient', ['id' => $id]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);

        } catch (NotAuthorizedException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');

        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'client' => $client
        ));
    }

    public function revokeTokenAction()
    {
        $id = $this->params()->fromRoute('client_id');
        try {
            $client = $this->apiService->findClientById($id);
        } catch (RecordNotFoundException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();

        } catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('detailClient', ['id' => $id]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);

        } catch (NotAuthorizedException $e) {																	//add
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');

        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', 'no');

            if ($del === 'yes') {
                $result = $this->apiService->revokeToken($client);
                if ($result === false) {
                    $this->logger->err('ApiController. Client (' . $client['client_id'] . ')' . $token . ' not revoked');

                    return $this->notFoundAction();
                }
            }

            return $this->redirect()->toRoute('listClients');	//TODO учитывать страницу, id и т.д.
        }

        return new ViewModel(array(
            'client' => $client
        ));
    }

    public function listClientsAction()
    {
        try {
            $clients = $this->apiService->findAllClients();
	} catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('listClients', ['page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);

        } catch (NotAuthorizedException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');

	} catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        return new ViewModel([
            'clients' => $clients,
            'route' => 'listClients'
        ]);
    }

    public function deleteClientAction()
    {
        $id = $this->params()->fromRoute('client_id');
        try {
            $client = $this->apiService->findClientById($id);
        } catch (RecordNotFoundException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();

        } catch (NotAuthenticatedException $e) {
            $redirectUrl = $this->url()->fromRoute('detailClient', ['id' => $id]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);

        } catch (NotAuthorizedException $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->redirect()->toRoute('notAuthorized');

        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', 'no');

            if ($del === 'yes') {
                $result = $this->apiService->deleteClient($client);
                if ($result === false) {
                    $this->logger->err('Client ' . $client['client_id'] . ' not deleted');

                    return $this->redirect()->toRoute('detailClient', ['id' => $id]);
                }
            }

            return $this->redirect()->toRoute('listClients');
        }

        return new ViewModel([
            'client' => $client
        ]);
    }
}

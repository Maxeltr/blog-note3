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
use MxmApi\Service\ApiServiceInterface;
use Zend\Form\FormInterface;
use MxmApi\Exception\AlreadyExistsException;
use MxmUser\Service\UserServiceInterface;
use Zend\i18n\Translator\TranslatorInterface;

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
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authService;

    /**
     * @var MxmUser\Service\UserServiceInterface
     */
    protected $userService;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(
        ApiServiceInterface $apiService,
        FormInterface $addClientForm,
        UserServiceInterface $userService,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->apiService = $apiService;
        $this->addClientForm = $addClientForm;
        $this->userService = $userService;
        $this->logger = $logger;
        $this->translator = $translator;
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
                    $this->logger->err('ApiController. Client already exists. ' . $e->getMessage());

                    return new ViewModel([
                        'form' => $this->addClientForm,
                        'error' => 'Client has registered alredy.'
                    ]);

                }

                return $this->redirect()->toRoute('detailClient',
                    ['client_id' => $savedClient->getClientId()]
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
        $client = $this->apiService->findClientById($id);

        return new ViewModel([
            'client' => $client
        ]);
    }

    public function revokeTokenAction()
    {
        $id = $this->params()->fromRoute('client_id');
        $client = $this->apiService->findClientById($id);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', $this->translator->translate('No'));

            if ($del === $this->translator->translate('Yes')) {
                $result = $this->apiService->revokeToken($client);
                if ($result === false) {
                    $this->logger->err('ApiController. Client (' . $client->getClientId() . ') token not revoked');

                    return $this->notFoundAction();
                }
            }

            return $this->redirect()->toRoute('listClients');
        }

        return new ViewModel([
            'client' => $client
        ]);
    }

    public function listClientsAction()
    {
        $clients = $this->apiService->findAllClients();

        return new ViewModel([
            'clients' => $clients,
            'route' => 'listClients'
        ]);
    }

    public function listClientsByUserAction()
    {
        $userId = $this->params()->fromRoute('id');
        $user = $this->userService->findUserById($userId);

        $clients = $this->apiService->findClientsByUser($user);

        $model = new ViewModel([
            'clients' => $clients,
            'route' => 'listClientsByUser'
        ]);
        $model->setTemplate('mxm-api/api/list-clients');

        return $model;
    }

    public function deleteClientAction()
    {
        $id = $this->params()->fromRoute('client_id');
        $client = $this->apiService->findClientById($id);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', $this->translator->translate('No'));

            if ($del === $this->translator->translate('Yes')) {
                $result = $this->apiService->deleteClient($client);
                if ($result === false) {
                    $this->logger->err('Client ' . $client->getClientId() . ' not deleted');

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

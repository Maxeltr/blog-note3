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

namespace MxmApi\Service;

use Zend\Paginator\Adapter;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\TableGateway\TableGateway;
use MxmUser\Model\UserInterface;
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use MxmRbac\Service\AuthorizationService;
use MxmApi\Exception\RuntimeException;
use MxmApi\Exception\ExpiredException;
use MxmApi\Exception\NotAuthenticatedException;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\RecordNotFoundException;
use MxmApi\Exception\AlreadyExistsException;
use MxmApi\Exception\InvalidPasswordException;
use MxmApi\Exception\NotAuthorizedException;
use MxmApi\Exception\DataBaseErrorException;
use Zend\Validator\Db\RecordExists;

class ApiService implements ApiServiceInterface
{
    /**
     * @var DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Crypt\Password\Bcrypt
     */
    protected $bcrypt;

    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $oauthClientsTableGateway;

    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $oauthAccessTokensTableGateway;

    /**
     * @var Zend\Validator\Db\RecordExists
     */
    protected $clientExistsValidator;

    /**
     * @var Zend\Config\Config;
     */
    protected $grantTypes;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        TableGateway $oauthClientsTableGateway,
        TableGateway $oauthAccessTokensTableGateway,
        RecordExists $clientExistsValidator,
        Config $grantTypes
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->oauthClientsTableGateway = $oauthClientsTableGateway;
        $this->oauthAccessTokensTableGateway = $oauthAccessTokensTableGateway;
        $this->clientExistsValidator = $clientExistsValidator;
        $this->grantTypes = $grantTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function addClient($data)
    {
        if (empty($data) or !is_array($data)) {
            throw new InvalidArgumentException('Data must be array and cannot be empty.');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('add.client.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "add.client.rest" is required.');
        }

        if ($this->clientExistsValidator->isValid($data['client_id'])) {
            throw new AlreadyExistsException("Client with " . $data['client_id'] . " already exists");
        }

        $this->oauthClientsTableGateway->insert([
            'client_id' => $data['client_id'],
            'client_secret' => $this->bcrypt->create($data['client_secret']),
            'grant_types' => $this->grantTypes[$data['grant_types']],
            'scope' => $data['scope'],
            'user_id' => $this->authenticationService->getIdentity()->getId()
        ]);

        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $data['client_id']]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        }

	return $resultSet->current();
    }

    public function findClientById($client_id)
    {
        if (empty($client_id)) {
            throw new InvalidArgumentException('The client_id cannot be empty.');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('find.client.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "find.client.rest" is required.');
        }

        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $client_id]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('Client ' . $client['client_id'] . 'not found.');
        }

	return $resultSet->current();
    }

    public function revokeToken($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('The client cannot be empty.');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('revoke.token.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "revoke.token.rest" is required.');
        }

        return $this->oauthAccessTokensTableGateway->delete(['client_id' => $client['client_id']]);
    }

    public function findAllClients()
    {
        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('find.clients.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "find.clients.rest" is required.');
        }

        $resultSet = $this->oauthClientsTableGateway->select();
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Select operation failed.");
        }

        $paginator = new Paginator(new Adapter\ArrayAdapter($resultSet->toArray()));

        return $paginator;
    }

    public function deleteClient($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('Client is requried');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('delete.client.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "delete.client.rest" is required.');
        }

        $this->revokeToken($client);

        return $this->oauthClientsTableGateway->delete(['client_id' => $client['client_id']]);;
    }
}
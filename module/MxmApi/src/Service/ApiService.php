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
use Zend\Paginator\Adapter\DbTableGateway;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use MxmApi\Model\Client;
use MxmApi\Mapper\ZendTableGatewayMapper;
use MxmApi\Model\ClientInterface;

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
     * @var Zend\Validator\Db\RecordExists
     */
    protected $clientExistsValidator;

    /**
     * @var Zend\Config\Config;
     */
    protected $grantTypes;

    /**
     * @var MxmUser\Mapper\MapperInterface
     */
    protected $userMapper;

    /**
     * @var MxmApi\Mapper\ZendTableGatewayMapper
     */
    protected $apiMapper;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        RecordExists $clientExistsValidator,
        UserMapperInterface $userMapper,
        ZendTableGatewayMapper $apiMapper,
        Config $grantTypes
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->clientExistsValidator = $clientExistsValidator;
        $this->userMapper = $userMapper;
        $this->apiMapper = $apiMapper;
        $this->grantTypes = $grantTypes;
    }

    /**
     * {@inheritDoc}
     */
    public function addClient($data)
    {
        if (! $data instanceof ClientInterface) {
            throw new InvalidArgumentException(sprintf(
                'The data must be ClientInterface object; received "%s"',
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('add.client.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "add.client.rest" is required.');
        }

        if ($this->clientExistsValidator->isValid($data->getClientId())) {
            throw new AlreadyExistsException("Client with " . $data->getClientId() . " already exists");
        }

        $data->setClientSecret($this->bcrypt->create($data->getClientSecret()));
        $data->setUserId($this->authenticationService->getIdentity()->getId());

        return $this->apiMapper->insertClient($data);

    }

    public function findClientById($clientId)
    {
        if (empty($clientId)) {
            throw new InvalidArgumentException('The client_id cannot be empty.');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        $client = $this->apiMapper->findClientById($clientId);

        $user = $this->userMapper->findUserById($client->getUserId());

        if (!$this->authorizationService->isGranted('find.client.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "find.client.rest" is required.');
        }

	return $client;
    }

    public function revokeToken($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('The client cannot be empty.');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        $user = $this->userMapper->findUserById($client->getUserId());

        if (!$this->authorizationService->isGranted('revoke.token.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "revoke.token.rest" is required.');
        }

        return $this->apiMapper->deleteToken($client);
    }

    public function findAllClients()
    {
        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('find.clients.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "find.clients.rest" is required.');
        }

        return $this->apiMapper->findAllClients();;
    }

    public function deleteClient($client)
    {
        if (empty($client)) {
            throw new InvalidArgumentException('Client is requried');
        }

        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        $user = $this->userMapper->findUserById($client->getUserId());

        if (!$this->authorizationService->isGranted('delete.client.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "delete.client.rest" is required.');
        }

        $this->revokeToken($client);

        return $this->apiMapper->deleteClient($client);
    }

    public function findAllFiles()
    {
        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('fetch.files.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "fetch.files.rest" is required.');
        }

        return $this->apiMapper->findAllFiles();
    }

    public function findAllFilesByUser(UserInterface $user = null)
    {
        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('fetch.files.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "fetch.files.rest" is required.');
        }

        return $this->apiMapper->findAllFiles($user);
    }
}
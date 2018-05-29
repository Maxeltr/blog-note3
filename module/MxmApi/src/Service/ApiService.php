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

use Zend\Config\Config;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Model\UserInterface;
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\AlreadyExistsException;
use MxmApi\Exception\NotAuthorizedException;
use Zend\Validator\Db\RecordExists;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use MxmApi\Mapper\ZendTableGatewayMapper;
use MxmApi\Model\ClientInterface;
use Zend\Log\Logger;
use Zend\Paginator\Paginator;
use Zend\Stdlib\ArrayUtils;
use MxmApi\V1\Rest\File\FileEntity;
use MxmApi\Exception\RuntimeException;
use Zend\Http\Response;

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

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var Zend\Http\Response
     */
    protected $response;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        RecordExists $clientExistsValidator,
        UserMapperInterface $userMapper,
        ZendTableGatewayMapper $apiMapper,
        Config $grantTypes,
        Logger $logger,
        Response $response
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->clientExistsValidator = $clientExistsValidator;
        $this->userMapper = $userMapper;
        $this->apiMapper = $apiMapper;
        $this->grantTypes = $grantTypes;
        $this->logger = $logger;
        $this->response = $response;
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

        $this->authenticationService->checkIdentity();

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

        $this->authenticationService->checkIdentity();

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

        $this->authenticationService->checkIdentity();

        $user = $this->userMapper->findUserById($client->getUserId());

        if (!$this->authorizationService->isGranted('revoke.token.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "revoke.token.rest" is required.');
        }

        return $this->apiMapper->deleteToken($client);
    }

    public function revokeTokens($clients)
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('revoke.tokens.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "revoke.tokens.rest" is required.');
        }

        if (! is_array($clients)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array; received "%s"',
                (is_object($clients) ? get_class($clients) : gettype($clients))
            ));
        }

        if (empty($clients)) {
            throw new InvalidArgumentException('The data array is empty');
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof ClientInterface) {
                return $value->getClientId();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of ClientInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $clientIdArray = array_map($func, $clients);

        return $this->apiMapper->deleteTokens($clientIdArray);
    }

    public function findAllClients()
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('find.clients.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "find.clients.rest" is required.');
        }

        return $this->apiMapper->findAllClients();;
    }

    public function findClientsByUser($user)
    {
        $this->authenticationService->checkIdentity();

        if (! $this->authorizationService->isGranted('find.clients.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "find.clients.rest" is required.');
        }

        return $this->apiMapper->findClientsByUser($user);
    }

    public function deleteClient(ClientInterface $client)
    {
        $this->authenticationService->checkIdentity();

        $user = $this->userMapper->findUserById($client->getUserId());

        if (!$this->authorizationService->isGranted('delete.client.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "delete.client.rest" is required.');
        }

        $this->revokeToken($client);

        return $this->apiMapper->deleteClient($client);
    }

    public function deleteClients($clients)
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('delete.clients.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "delete.clients.rest" is required.');
        }

        if ($clients instanceof Paginator) {
            $clients = iterator_to_array($clients);
        }

        if (! is_array($clients)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array; received "%s"',
                (is_object($clients) ? get_class($clients) : gettype($clients))
            ));
        }

        if (empty($clients)) {
            throw new InvalidArgumentException('The data array is empty');
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof ClientInterface) {
                return $value->getClientId();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of ClientInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $clientId = array_map($func, $clients);

        $this->apiMapper->deleteTokens($clientId);
        $this->apiMapper->deleteClients($clientId);

        return;
    }

    public function findAllFiles()
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('fetch.files.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "fetch.files.rest" is required.');
        }

        return $this->apiMapper->findAllFiles();
    }

    public function findAllFilesByUser(UserInterface $user = null)
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('fetch.files.rest', $user)) {
            throw new NotAuthorizedException('Access denied. Permission "fetch.files.rest" is required.');
        }

        return $this->apiMapper->findAllFiles($user);
    }

    public function deleteFiles($files)
    {
        $this->authenticationService->checkIdentity();

        if (!$this->authorizationService->isGranted('delete.files.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "delete.files.rest" is required.');
        }

        if ($files instanceof Paginator) {
            $files = ArrayUtils::iteratorToArray($files->setItemCountPerPage(-1));
        }

        if (! is_array($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        if (empty($files)) {
            throw new InvalidArgumentException('The data array is empty');
        }

        $this->apiMapper->deleteFiles($files);

        return;
    }

    public function downloadFile($fileId)
    {
         $this->authenticationService->checkIdentity();

        if (! $this->authorizationService->isGranted('download.file.rest')) {
            throw new NotAuthorizedException('Access denied. Permission "download.file.rest" is required.');
        }

        if (! is_string($fileId)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($fileId) ? get_class($fileId) : gettype($fileId))
            ));
        }

        $file = $this->apiMapper->findFileById($fileId);

        $path = $file->getPath();

        if (!is_readable($path)) {
            throw new RuntimeException('Path "' . $path . '" is not readable.');
        }

        if (! is_file($path)) {
            throw new RuntimeException('File "' . $path . '" does not exist.');
        }

        $headers = $this->response->getHeaders();
        $headers->addHeaderLine("Content-type: application/octet-stream");
        $headers->addHeaderLine("Content-Disposition: attachment; filename=\"" . $file->getFilename() . "\"");
        $headers->addHeaderLine("Content-length: " . filesize($path));
//        $headers->addHeaderLine("Cache-control: private"); //use this to open files directly

        $fileContent = file_get_contents($path);
        if ($fileContent !== false) {
            $this->response->setContent($fileContent);
        } else {
            throw new RuntimeException("Can't read file");
        }

        return $this->response;
    }
}
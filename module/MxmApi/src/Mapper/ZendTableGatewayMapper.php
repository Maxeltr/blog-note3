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

namespace MxmApi\Mapper;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\PreparableSqlInterface;
use MxmApi\Exception\RecordNotFoundException;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\DataBaseErrorException;
use MxmUser\Model\UserInterface;
use Zend\Hydrator\HydratorInterface;
use MxmApi\Model\Client;
use Zend\Paginator\Adapter\DbTableGateway;

class ZendTableGatewayMapper //implements MapperInterface
{
    /**
     * @var Zend\Config\Config;
     */
    protected $config;

    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $oauthClientsTableGateway;

    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $oauthAccessTokensTableGateway;

    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $fileTableGateway;

    /**
     * @var Zend\Hydrator\HydratorInterface
     */
    protected $clientHydrator;

    public function __construct(
        TableGateway $oauthClientsTableGateway,
        TableGateway $oauthAccessTokensTableGateway,
        TableGateway $fileTableGateway,
        HydratorInterface $clientHydrator,
        Config $config
    ) {
        $this->oauthClientsTableGateway = $oauthClientsTableGateway;
        $this->oauthAccessTokensTableGateway = $oauthAccessTokensTableGateway;
        $this->fileTableGateway = $fileTableGateway;
        $this->clientHydrator = $clientHydrator;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function insertClient(Client $client)
    {
        $data = $this->clientHydrator->extract($client);

        $this->oauthClientsTableGateway->insert($data);

        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $client->getClientId()]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    public function findClientById($clientId)
    {
        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $clientId]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('Client ' . $clientId . 'not found.');
        }

        return $resultSet->current();
    }

    public function findAllClients()
    {
        $paginatorAdapter = new DbTableGateway($this->oauthClientsTableGateway);
        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    public function deleteToken($client)
    {
        return $this->oauthAccessTokensTableGateway->delete(['client_id' => $client->getClientId()]);
    }
}
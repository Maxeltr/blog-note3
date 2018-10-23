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

use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;
use Zend\Paginator\Paginator;
use MxmApi\Exception\RecordNotFoundException;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\DataBaseErrorException;
use MxmUser\Model\UserInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Adapter\DbTableGateway;
use MxmApi\Model\ClientInterface;
use Zend\Db\Sql\Where;
use Zend\Log\Logger;

class ZendTableGatewayMapper implements MapperInterface
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
     * @var Zend\Hydrator\HydratorInterface
     */
    protected $clientHydrator;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(
        TableGateway $oauthClientsTableGateway,
        TableGateway $oauthAccessTokensTableGateway,
        HydratorInterface $clientHydrator,
        Config $config,
        Logger $logger
    ) {
        $this->oauthClientsTableGateway = $oauthClientsTableGateway;
        $this->oauthAccessTokensTableGateway = $oauthAccessTokensTableGateway;
        $this->clientHydrator = $clientHydrator;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function insertClient(ClientInterface $client)
    {
        $data = $this->clientHydrator->extract($client);

        $this->oauthClientsTableGateway->insert($data);

        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $client->getClientId()]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function findClientById($clientId)
    {
        $resultSet = $this->oauthClientsTableGateway->select(['client_id' => $clientId]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('Client ' . $clientId . 'not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function findAllClients()
    {
        $paginatorAdapter = new DbTableGateway($this->oauthClientsTableGateway);
        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function findClientsByUser(UserInterface $user)
    {
        $paginatorAdapter = new DbTableGateway($this->oauthClientsTableGateway, ['user_id' => $user->getId()]);
        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteToken(ClientInterface $client)
    {
        return $this->oauthAccessTokensTableGateway->delete(['client_id' => $client->getClientId()]);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTokens($clients)
    {
        $clientIds = $this->getClientIds($clients);
        if (empty($clientIds)) {

            return 0;
        }

        $where = new Where();
        $where->in('client_id', $clientIds);

        return $this->oauthAccessTokensTableGateway->delete($where);
    }

    private function getClientIds($clients)
    {
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
            return [];
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

        $clientIds = array_map($func, $clients);

        return $clientIds;
    }

    public function deleteClient(ClientInterface $client)
    {
        return $this->oauthClientsTableGateway->delete(['client_id' => $client->getClientId()]);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteClients($clients)
    {
        $clientIds = $this->getClientIds($clients);
        if (empty($clientIds)) {

            return 0;
        }

        $where = new Where();
        $where->in('client_id', $clientIds);

        return $this->oauthClientsTableGateway->delete($where);
    }
}
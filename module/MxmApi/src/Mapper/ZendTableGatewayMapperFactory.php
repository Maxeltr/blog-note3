<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\Reflection as ReflectionHydrator;
use Zend\Hydrator\NamingStrategy\UnderscoreNamingStrategy;
use Zend\Config\Config;
use Zend\Db\Adapter\Adapter;
use MxmApi\Model\ClientInterface;
use MxmApi\V1\Rest\File\FileEntity;
use Zend\Db\ResultSet\HydratingResultSet;
use MxmApi\Model\Client;
use Zend\Db\TableGateway\TableGateway;
use MxmApi\Hydrator\ClientMapperHydrator;
use MxmApi\Logger;

class ZendTableGatewayMapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get(Adapter::class);
        $logger = $container->get(Logger::class);

        $fileHydrator = new ReflectionHydrator();
        $fileResultSet = new HydratingResultSet($fileHydrator, new FileEntity());
        $fileTableGateway = new TableGateway('files', $dbAdapter, null, $fileResultSet);

        $clientHydrator = $container->get(ClientMapperHydrator::class);
        $clientResultSet = new HydratingResultSet($clientHydrator, new Client());
        $oauthClientsTableGateway = new TableGateway('oauth_clients', $dbAdapter, null, $clientResultSet);

        $oauthAccessTokensTableGateway = new TableGateway('oauth_access_tokens', $dbAdapter, null, null);

        $config = new Config($container->get('config'));

        return new ZendTableGatewayMapper(
            $oauthClientsTableGateway,
            $oauthAccessTokensTableGateway,
            $fileTableGateway,
            $clientHydrator,
            $config,
            $logger
        );
    }
}
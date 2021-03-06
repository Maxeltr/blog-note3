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

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use MxmApi\Service\ApiService;
use Laminas\Authentication\AuthenticationService;
use Laminas\Crypt\Password\Bcrypt;
use MxmRbac\Service\AuthorizationService;
use Laminas\Db\Adapter\Adapter;
use Laminas\Config\Config;
use Laminas\Validator\Db\RecordExists;
use MxmApi\Logger;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use MxmApi\Mapper\MapperInterface as ApiMapperInterface;
use Laminas\Http\Response;
use MxmFile\Mapper\MapperInterface as FileMapperInterface;

class ApiServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authorizationService = $container->get(AuthorizationService::class);
        $dateTime = $container->get('datetime');
        $authService = $container->get(AuthenticationService::class);
        $dbAdapter = $container->get(Adapter::class);
        $bcrypt = new Bcrypt();

        $config = new Config($container->get('config'));
        $grantTypes = $config->mxm_api->grant_types;

        $recordExistsValidator = new RecordExists([
            'table'   => 'oauth_clients',
            'field'   => 'client_id',
            'adapter' => $dbAdapter,
        ]);

        $userMapper = $container->get(UserMapperInterface::class);
        $fileMapper = $container->get(FileMapperInterface::class);
        $apiMapper = $container->get(ApiMapperInterface::class);
        $logger = $container->get(Logger::class);
        $response = new Response();

        return new ApiService(
            $dateTime,
            $authService,
            $authorizationService,
            $bcrypt,
            $recordExistsValidator,
            $userMapper,
            $apiMapper,
            $fileMapper,
            $grantTypes,
            $logger,
            $response
        );
    }
}
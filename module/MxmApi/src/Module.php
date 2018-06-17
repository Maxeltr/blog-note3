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

namespace MxmApi;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use MxmUser\Service\UserServiceInterface;
use MxmApi\Mapper\MapperInterface as ApiMapperInterface;
use MxmApi\Service\ApiServiceInterface;

class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    public function onBootstrap(EventInterface $event)
    {
        $application = $event->getTarget();
        $serviceManager = $application->getServiceManager();

        $apiMapper = $serviceManager->get(ApiMapperInterface::class);
        $apiService = $serviceManager->get(ApiServiceInterface::class);
        $usEventManager = $serviceManager->get(UserServiceInterface::class)->getEventManager();
        $usEventManager->attach('deleteUser',
            function (EventInterface $event) use ($apiMapper, $apiService) {
                $user = $event->getParam('user');
                $clients = $apiMapper->findClientsByUser($user)->setItemCountPerPage(-1);
                $apiMapper->deleteClients($clients);
                $files = $apiMapper->findAllFilesByUser($user)->setItemCountPerPage(-1);
                $apiService->deleteFiles($files);
            }
        );

    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}

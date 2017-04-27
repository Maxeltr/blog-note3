<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

namespace MxmRbac\Factory\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Config\Config;
use Zend\Validator\InArray;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmRbac\Assertion\AssertionPluginManager;
use MxmRbac\Exception\InvalidArgumentRbacException;
use MxmRbac\Logger;

class AuthorizationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authenticationService = $container->get(AuthenticationService::class);
        $validator = new InArray();
        $logger = $container->get(Logger::class);

        $config = new Config($container->get('config'));
        if (!isset($config->rbac_module->rbac_config->assertions)) {
            throw new InvalidArgumentRbacException("Invalid options. No assertions in config file.");
        }

        $rbac = new Rbac();
        $roles = $config->rbac_module->rbac_config->roles;
        foreach ($roles as $name => $value) {
            $role = new Role($name);
            foreach ($value->permissions as $permission) {
                $role->addPermission($permission);
            }
            $rbac->addRole($role, $value->parent);
        }

        $currentUser = $authenticationService->getIdentity();

        $assertionPluginManager = $container->get(AssertionPluginManager::class);

        $authorizationService = new AuthorizationService($rbac, $assertionPluginManager, $config->rbac_module->rbac_config, $validator, $logger, $currentUser);

        return $authorizationService;
    }

}
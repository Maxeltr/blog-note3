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

namespace MxmRbac\Service;

use MxmUser\Model\UserInterface;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\RoleInterface;
use Zend\Permissions\Rbac\Rbac;
use MxmRbac\Assertion\AssertionPluginManager;

class AuthorizationService
{
    /*
     * @var string Name of Role
     */
    protected $currentUserRole;

    /*
     * @var Zend\Permissions\Rbac\Rbac
     */
    protected $rbac;

    /*
     * @var Zend\Permissions\Rbac\Rbac
     */
    protected $assertionPluginManager;


    public function __construct(Rbac $rbac, AssertionPluginManager $assertionPluginManager, $currentUser = null)
    {
        if ($currentUser instanceof UserInterface) {
            $this->currentUserRole = $currentUser->getRole();
        } else {
            $this->currentUserRole = 'anonymous';   //TODO брать роль из настроек?
        }
        $this->rbac = $rbac;
        $this->assertionPluginManager = $assertionPluginManager;
    }

    /**
     * Check if the permission is granted to the current identity
     *
     * @param string $permission
     * @param AssertionInterface $assertion
     *
     * @return bool
     */
    public function isGranted($permission, $assertion, $context)
    {

        $assertion = $this->assertionPluginManager->get($assertion);

        $this->rbac->isGranted($this->currentUserRole, $permission, $assertion);

        return true;
    }


}

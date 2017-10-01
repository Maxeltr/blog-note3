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
use Zend\Config\Config;
use Zend\Validator\InArray;
use Zend\Log\Logger;

class AuthorizationService
{
    /*
     * @var string Name of Role
     */
    protected $currentUser;

    /*
     * @var Zend\Permissions\Rbac\Rbac
     */
    protected $rbac;

    /*
     * @var Zend\Permissions\Rbac\Rbac
     */
    protected $assertionPluginManager;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /*
     * var Zend\Validator\InArray
     */
    protected $inArrayValidator;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    protected $assertions = [
	'MustBeAuthorAssertion',
	'AssertUserIdMatches',
    ];

    public function __construct
    (
        Rbac $rbac,
        AssertionPluginManager $assertionPluginManager,
        Config $config,
        InArray $inArrayValidator,
        Logger $logger,
        UserInterface $currentUser = null
    ) {
        $this->currentUser = $currentUser;
        $this->rbac = $rbac;
        $this->assertionPluginManager = $assertionPluginManager;
         $this->config = $config;
        $this->inArrayValidator = $inArrayValidator;
        $this->logger = $logger;
    }

    /**
     * Check if the permission is granted to the current identity
     *
     * @param string $permission
     * @param mixed $content
     *
     * @return bool
     */
    public function isGranted($permission, $content = null)
    {

        if (empty($this->currentUser)) {
            return false;
        }

        $role = $this->currentUser->getRole();
        if (!$this->rbac->hasRole($role)) {
            return false;
        }

        if ($this->config->roles->$role->get('no_assertion', false)) {             //option 'no_assertion' is present?
            return $this->checkIsGranted($role, $permission);
        }

        $isGranted = true;
        $assertionNames = $this->getAssertionNames($permission);                    //get assertions for given permission
        if (count($assertionNames) < 1) {                                           //assertions for given permission is absent?
            $isGranted = $this->checkIsGranted($role, $permission);
        } else {
            foreach ($assertionNames as $assertionName) {
                if ($content === null) {
                    return false;
                }
                $assertion = $this->assertionPluginManager->get($assertionName);
                $assertion->setCurrentUser($this->currentUser);
                $assertion->setContent($content);
                $isGranted = $isGranted && $this->checkIsGranted($role, $permission, $assertion);
            }
        }

        return $isGranted;
    }

    private function checkIsGranted($role, $permission, $assertion = null)
    {
	try {
            $isGranted = $this->rbac->isGranted($role, $permission, $assertion);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            $isGranted = false;
        }

	return $isGranted;
    }

    /**
     * Get assertion from module.config
     *
     * @param string $permission
     *
     * @return array $assertions
     */
    private function getAssertionNames($permission)
    {
        $assertions = [];
	foreach ($this->assertions as $assertion) {
            if (isset($this->config->assertions->$assertion) && $this->config->assertions->$assertion->permissions) {		//TODO проверить очередность
		$this->inArrayValidator->setHaystack($this->config->assertions->$assertion->permissions->toArray());
                if ($this->inArrayValidator->isValid($permission)) {
                    $assertions[] = $assertion;
		}
            }
	}

	return $assertions;
    }
}

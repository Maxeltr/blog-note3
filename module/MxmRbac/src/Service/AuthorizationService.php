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
use MxmRbac\Exception\InvalidArgumentRbacException;

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


    public function __construct(Rbac $rbac, AssertionPluginManager $assertionPluginManager, Config $config, InArray $inArrayValidator, $currentUser = null)
    {
        if ($currentUser instanceof UserInterface) {
            $this->currentUser = $currentUser;
        }

        $this->rbac = $rbac;
        $this->assertionPluginManager = $assertionPluginManager;
        $this->config = $config;
        $this->inArrayValidator = $inArrayValidator;
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
        
        if (empty($this->currentUser->getRole())) {
            return false;
        }

        $assertionName = $this->getAssertionName($permission);
        if ($assertionName) {
            $assertion = $this->assertionPluginManager->get($assertionName);
            $assertion->setCurrentUser($this->currentUser);
            $assertion->setContent($content);
        } else {
            $assertion = null;
        }

        return $this->rbac->isGranted($this->currentUser->getRole(), $permission, $assertion);
    }

    /**
     * Get assertion from module.config
     *
     * @param string $permission
     *
     * @return mixed string $assertion|null
     */
    private function getAssertionName($permission)
    {
        if (!isset($this->config->rbac_config->assertions)) {
            throw new InvalidArgumentRbacException("Invalid options. No assertions in config file.");
        }
        
        foreach ($this->config->rbac_config->assertions as $assertion) {
            if (!isset($assertion->permissions)) {
                break;
            }
            
            $this->inArrayValidator->setHaystack($assertion->permissions->toArray());
            if ($this->inArrayValidator->isValid($permission)) {
                
                return isset($assertion->name) ? $assertion->name : null;
            }  
        }
        
        return null;
    }
}

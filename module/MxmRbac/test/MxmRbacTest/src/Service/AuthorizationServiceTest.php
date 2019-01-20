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

namespace MxmRbacTest\Service;

use MxmUser\Model\UserInterface;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\RoleInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use MxmRbac\Assertion\AssertionPluginManager;
use Zend\Config\Config;
use Zend\Validator\InArray;
use Zend\Log\Logger;
use MxmRbac\Service\AuthorizationService;
use Prophecy\Argument;
use MxmBlog\Model\Post;
use MxmUser\Model\User;
use MxmRbac\Assertion\AssertUserIdMatches;
use MxmRbac\Assertion\MustBeAuthorAssertion;
use MxmRbac\Exception\InvalidArgumentException;

class AuthorizationServiceTest extends \PHPUnit\Framework\TestCase
{
    protected $currentUser;
    protected $rbac;
    protected $assertionPluginManager;
    protected $assertions;
    protected $config;
    protected $inArrayValidator;
    protected $logger;
    protected $traceError = true;
    protected $authorizationService;
    protected $assertUserIdMatches;
    protected $mustBeAuthorAssertion;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $configArray = [
            'roles' => [
                'admin' => [
                    'parent' => '',
                    'no_assertion' => true,
                    'permissions' => [
                    ]
                ],
                'moderator' => [
                    'parent' => 'admin',
                    //'no_assertion' => true,
                    'permissions' => [
                        'add.category',
                        'edit.category',
                        'delete.category',
                        'add.tag',
                        'edit.tag',
                        'delete.tag',
                        'find.users'
                    ]
                ],
                'author' => [
                    'parent' => 'moderator',
                    'permissions' => [
                        'add.post',
                        'edit.post',
                        'delete.post',

                    ],
                ],
                'user' => [
                    'parent' => 'author',
                    'permissions' => [
                        'find.user',
                        'edit.user',
                        'delete.user',
                        'edit.password',
                        'edit.email',
                    ]
                ],
            ],
            'assertions' => [
                'MustBeAuthorAssertion' => [
                    'permissions' => [
                        'edit.post',
                        'delete.post',
                    ]
                ],
                'AssertUserIdMatches' => [
                    'permissions' => [
                        'find.user',
                        'edit.user',
                        'delete.user',
                    ]
                ],
            ],
        ];

        $this->config = new Config($configArray);

        $this->rbac = new Rbac();
        $roles = $this->config->roles;
        foreach ($roles as $name => $value) {
            $role = new Role($name);
            foreach ($value->permissions as $permission) {
                $role->addPermission($permission);
            }
            $this->rbac->addRole($role, $value->parent);
        }

        $this->assertionPluginManager = $this->prophesize(AssertionPluginManager::class);
        $this->inArrayValidator = $validator = new InArray();
        $this->logger = $this->prophesize(Logger::class);

        $this->currentUser = new User();
        $this->currentUser->setId('1');
        $this->currentUser->setEmail('testEmail@test.ru');
        $this->currentUser->setPassword('testPassword');
        $this->currentUser->setRole('user');

        $this->assertUserIdMatches = new AssertUserIdMatches();
        $this->assertUserIdMatches->setIdentity($this->currentUser);

        $this->mustBeAuthorAssertion = new MustBeAuthorAssertion();
        $this->mustBeAuthorAssertion->setIdentity($this->currentUser);

        $this->authorizationService = new AuthorizationService(
            $this->rbac,
            $this->assertionPluginManager->reveal(),
            $this->config,
            $this->inArrayValidator,
            $this->logger->reveal(),
            $this->currentUser
        );

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenIdsMatch()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->isGranted('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenIdsDoNotMatch()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('2');
        $this->assertSame(false, $this->authorizationService->isGranted('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenPermissionDoesNotExist()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->expectException(InvalidArgumentException::class);
        $this->authorizationService->isGranted('nonexistent.permission', $user);
        //$this->assertSame(false, $this->authorizationService->isGranted('nonexistent.permission', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenContentIsAbsent()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->assertSame(false, $this->authorizationService->isGranted('find.user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenRoleIsAbsent()
    {
        $user = new User();
        $user->setId('1');
        $user->setRole('nonexistentRole');
        $authorizationService = new AuthorizationService(
            $this->rbac,
            $this->assertionPluginManager->reveal(),
            $this->config,
            $this->inArrayValidator,
            $this->logger->reveal(),
              $user
        );
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);

        $this->assertSame(false, $authorizationService->isGranted('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenCurrentUserIsAbsent()
    {
        $authorizationService = new AuthorizationService(
            $this->rbac,
            $this->assertionPluginManager->reveal(),
            $this->config,
            $this->inArrayValidator,
            $this->logger->reveal()
        );
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->assertSame(false, $authorizationService->isGranted('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWithNoAssertionsOption()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->currentUser->setRole('admin');
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->isGranted('find.user', $user));
        $user->setId('2');
        $this->assertSame(true, $this->authorizationService->isGranted('find.user', $user));
        $this->assertSame(true, $this->authorizationService->isGranted('find.user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWithNoAssertionsOptionWhenPermissionDoesNotExist()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->currentUser->setRole('admin');
        $user = new User();
        $user->setId('1');
        $this->assertSame(false, $this->authorizationService->isGranted('nonexistent.permission', $user));
        $user->setId('2');
        $this->assertSame(false, $this->authorizationService->isGranted('nonexistent.permission', $user));
        $this->assertSame(false, $this->authorizationService->isGranted('nonexistent.permission'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithMustBeAuthorAssertionWhenIdsMatch()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeAuthorAssertion);
        $this->currentUser->setRole('author');
        $user = new User();
        $user->setId('1');
        $post = new Post();
        $post->setAuthor($user);
        $this->assertSame(true, $this->authorizationService->isGranted('edit.post', $post));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithMustBeAuthorAssertionWhenIdsDoNotMatch()
    {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeAuthorAssertion);
        $this->currentUser->setRole('author');
        $user = new User();
        $user->setId('2');
        $post = new Post();
        $post->setAuthor($user);
        $this->assertSame(false, $this->authorizationService->isGranted('edit.post', $post));
    }
}

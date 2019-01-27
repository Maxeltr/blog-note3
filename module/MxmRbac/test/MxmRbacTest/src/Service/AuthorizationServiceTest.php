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
use MxmFile\Model\File;
use MxmRbac\Exception\NotAuthorizedException;
use MxmRbac\Assertion\MustBeOwnerAssertion;

class AuthorizationServiceTest extends \PHPUnit\Framework\TestCase {

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
    protected $mustBeOwnerAssertion;

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
                        'manage.role'
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
                        'find.users',
                        'do.any.moderator',
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
                'restUser' => [
                    'parent' => 'author',
                    'permissions' => [
                        'find.clients.rest',
                        'find.client.rest',
                        'add.client.rest',
                        'delete.client.rest',
                        'revoke.token.rest',
                        'fetch.file.rest',
                        'fetch.files.by.user.rest',
                        'delete.file.rest',
                        'create.file.rest',
                        'download.file.rest',
                    ]
                ],
                'user' => [
                    'parent' => 'restUser',
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
                        'do.any.moderator'
                    ]
                ],
                'MustBeOwnerAssertion' => [
                    'permissions' => [
                        'fetch.file.rest',
                        'delete.file.rest',
                        'download.file.rest',
                        'download.file',
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

        $this->mustBeOwnerAssertion = new MustBeOwnerAssertion();
        $this->mustBeOwnerAssertion->setIdentity($this->currentUser);

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
    public function testIsGrantedWithoutAssertions() {
        $this->assertSame(false, $this->authorizationService->isGranted('nonexistent.permission'));
        $this->assertSame(false, $this->authorizationService->isGranted('add.post'));

        $this->currentUser->setRole('author');
        $this->assertSame(true, $this->authorizationService->isGranted('add.post'));
        $this->assertSame(false, $this->authorizationService->isGranted('add.category'));

        $this->currentUser->setRole('moderator');
        $this->assertSame(true, $this->authorizationService->isGranted('add.post'));
        $this->assertSame(true, $this->authorizationService->isGranted('add.category'));
        $this->assertSame(false, $this->authorizationService->isGranted('manage.role'));

        $this->currentUser->setRole('admin');
        $this->assertSame(true, $this->authorizationService->isGranted('add.post'));
        $this->assertSame(true, $this->authorizationService->isGranted('add.category'));
        $this->assertSame(true, $this->authorizationService->isGranted('manage.role'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenPermissionDoesNotExist() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->expectException(InvalidArgumentException::class);
        $this->authorizationService->checkPermission('nonexistent.permission', $user);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenContentIsAbsent() {
        $this->expectException(NotAuthorizedException::class);
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->authorizationService->checkPermission('find.user');
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenRoleIsAbsent() {
        $this->expectException(NotAuthorizedException::class);
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->currentUser->setRole('');
        $this->authorizationService->checkPermission('find.user', $user);
        $this->currentUser->setRole('nonexistentRole');
        $this->authorizationService->checkPermission('find.user', $user);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenCurrentUserIsAbsent() {
        $this->expectException(NotAuthorizedException::class);
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
        $authorizationService->checkPermission('find.user', $user);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithMustBeAuthorAssertionWhenIdsMatchAndPermissionIsNotGranted() {
        $this->expectException(NotAuthorizedException::class);
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeAuthorAssertion);
        $user = new User();
        $user->setId('1');
        $post = new Post();
        $post->setAuthor($user);
        $this->authorizationService->checkPermission('edit.post', $post);
        $this->currentUser->setRole('author');
        $this->assertSame(true, $this->authorizationService->checkPermission('edit.post', $post));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchIdentityRoles
     *
     */
    public function testMatchIdentityRolesWhenCurrentUserIsAbsent() {
        $authorizationService = new AuthorizationService(
                $this->rbac,
                $this->assertionPluginManager->reveal(),
                $this->config,
                $this->inArrayValidator,
                $this->logger->reveal()
        );
        $this->assertSame(false, $authorizationService->matchIdentityRoles('user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchIdentityRoles
     *
     */
    public function testMatchIdentityRolesWhenRoleIsAbsent() {
        $this->currentUser->setRole('nonexistentRole');
        $this->assertSame(false, $this->authorizationService->matchIdentityRoles('user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchUserIds
     *
     */
    public function testMatchUserIdsWithInvalidArgument() {
        $this->expectException(InvalidArgumentException::class);
        $this->authorizationService->matchUserIds('user');
        $this->authorizationService->matchUserIds('1');
        $this->authorizationService->matchUserIds(1);
        $this->authorizationService->matchUserIds(null);
        $this->authorizationService->matchUserIds('');
        $post = new Post();
        $this->authorizationService->matchUserIds($post);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchUserIds
     *
     */
    public function testMatchUserIdsWhenCurrentUserIsAbsent() {
        $authorizationService = new AuthorizationService(
                $this->rbac,
                $this->assertionPluginManager->reveal(),
                $this->config,
                $this->inArrayValidator,
                $this->logger->reveal()
        );
        $user = new User();
        $user->setId('1');
        $this->assertSame(false, $authorizationService->matchUserIds($user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchUserIds
     *
     */
    public function testMatchUserIdsNotMatch() {
        $user = new User();
        $user->setId('2');
        $this->assertSame(false, $this->authorizationService->matchUserIds($user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchUserIds
     *
     */
    public function testMatchUserIds() {
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->matchUserIds($user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::setIdentity
     *
     */
    public function testSetIdentityWithInvalidArgument() {
        $this->expectException(\TypeError::class);
        $this->authorizationService->setIdentity('user');
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::setIdentity
     *
     */
    public function testSetAndGetIdentity() {
        $authorizationService = new AuthorizationService(
                $this->rbac,
                $this->assertionPluginManager->reveal(),
                $this->config,
                $this->inArrayValidator,
                $this->logger->reveal()
        );
        $this->assertSame(null, $authorizationService->getIdentity());
        $user = clone $this->currentUser;
        $this->assertEquals($this->authorizationService, $authorizationService->setIdentity($user));
        $this->assertSame($user, $authorizationService->getIdentity());
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchIdentityRoles
     */
    public function testMatchIdentityRolesWithInvalidArgument() {
        $this->expectException(InvalidArgumentException::class);
        $this->authorizationService->matchIdentityRoles(null);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::matchIdentityRoles
     */
    public function testMatchIdentityRoles() {
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles(new Role('user')));
        $this->assertSame(false, $this->authorizationService->matchIdentityRoles(new Role('author')));

        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('user'));
        $this->assertSame(false, $this->authorizationService->matchIdentityRoles('author'));
        $this->assertSame(false, $this->authorizationService->matchIdentityRoles('admin'));

        $this->currentUser->setRole('author');
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('user'));
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('author'));
        $this->assertSame(false, $this->authorizationService->matchIdentityRoles('admin'));
        $this->currentUser->setRole('admin');
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('user'));
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('author'));
        $this->assertSame(true, $this->authorizationService->matchIdentityRoles('admin'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithoutAssertionsWhenCurrentUserIsAbsent() {
        $this->expectException(NotAuthorizedException::class);
        $authorizationService = new AuthorizationService(
                $this->rbac,
                $this->assertionPluginManager->reveal(),
                $this->config,
                $this->inArrayValidator,
                $this->logger->reveal()
        );
        $authorizationService->checkPermission('add.post');
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithoutAssertionsWhenRoleIsAbsent() {
        $this->expectException(NotAuthorizedException::class);
        $this->currentUser->setRole('');
        $this->authorizationService->checkPermission('add.post');
        $this->currentUser->setRole('nonexistentRole');
        $this->authorizationService->checkPermission('add.post');
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithoutAssertionsWhenPermissionDoesNotExist() {
        $this->expectException(NotAuthorizedException::class);
        $this->authorizationService->checkPermission('nonexistent.permission');
        $this->authorizationService->checkPermission('');
        $this->authorizationService->checkPermission(null);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithoutAssertionsWhenPermissionIsNotGranted() {
        $this->expectException(NotAuthorizedException::class);
        $this->authorizationService->checkPermission('add.post');
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithoutAssertionsWhenPermissionIsGranted() {
        $this->currentUser->setRole('author');
        $this->assertSame(true, $this->authorizationService->checkPermission('add.post'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenIdsDoNotMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->expectException(NotAuthorizedException::class);
        $user = new User();
        $user->setId('2');
        $this->authorizationService->checkPermission('find.user', $user);
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     */
    public function testCheckPermissionWithAssertUserIdMatchesWhenIdsMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->checkPermission('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::checkPermission
     *
     */
    public function testCheckPermissionWithAssertUserIdMatchesWithNoAssertionsOption() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->currentUser->setRole('admin');
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->checkPermission('find.user', $user));
        $user->setId('2');
        $this->assertSame(true, $this->authorizationService->checkPermission('find.user', $user));
        $this->assertSame(true, $this->authorizationService->checkPermission('find.user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenIdsMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('1');
        $this->assertSame(true, $this->authorizationService->isGranted('find.user', $user));
        $this->assertSame(false, $this->authorizationService->isGranted('do.any.moderator', $user));
        $this->currentUser->setRole('moderator');
        $this->assertSame(true, $this->authorizationService->isGranted('do.any.moderator', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenIdsDoNotMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $user = new User();
        $user->setId('2');
        $this->assertSame(false, $this->authorizationService->isGranted('find.user', $user));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenPermissionDoesNotExist() {
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
    public function testIsGrantedWithAssertUserIdMatchesWhenContentIsAbsent() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->assertUserIdMatches);
        $this->assertSame(false, $this->authorizationService->isGranted('find.user'));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithAssertUserIdMatchesWhenRoleIsAbsent() {
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
    public function testIsGrantedWithAssertUserIdMatchesWhenCurrentUserIsAbsent() {
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
    public function testIsGrantedWithAssertUserIdMatchesWithNoAssertionsOption() {
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
    public function testIsGrantedWithAssertUserIdMatchesWithNoAssertionsOptionWhenPermissionDoesNotExist() {
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
    public function testIsGrantedWithMustBeAuthorAssertionWhenIdsMatch() {
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
    public function testIsGrantedWithMustBeAuthorAssertionWhenIdsDoNotMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeAuthorAssertion);
        $this->currentUser->setRole('author');
        $user = new User();
        $user->setId('2');
        $post = new Post();
        $post->setAuthor($user);
        $this->assertSame(false, $this->authorizationService->isGranted('edit.post', $post));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithMustBeOwnerAssertionWhenIdsMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeOwnerAssertion);
        $user = new User();
        $user->setId('1');
        $file = new File();
        $file->setOwner($user);
        $this->assertSame(false, $this->authorizationService->isGranted('fetch.file.rest', $file));
        $this->currentUser->setRole('restUser');
        $this->assertSame(true, $this->authorizationService->isGranted('fetch.file.rest', $file));
    }

    /**
     * @covers MxmRbac\Service\AuthorizationService::isGranted
     *
     */
    public function testIsGrantedWithMustBeOwnerAssertionWhenIdsDoNotMatch() {
        $this->assertionPluginManager->get(Argument::any())->willReturn($this->mustBeOwnerAssertion);
        $this->currentUser->setRole('restUser');
        $user = new User();
        $user->setId('22');
        $file = new File();
        $file->setOwner($user);
        $this->assertSame(false, $this->authorizationService->isGranted('fetch.file.rest', $file));
    }

}

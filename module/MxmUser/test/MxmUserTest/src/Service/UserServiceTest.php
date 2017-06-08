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

namespace MxmUserTest\Controller;

use MxmUser\Mapper\MapperInterface;
use MxmUser\Model\UserInterface;
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Validator\Db\RecordExists;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;
use MxmUser\Exception\RuntimeUserException;
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use Zend\Math\Rand;
use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Transport\Sendmail as SendMailTransport;
use Zend\Mime\Part as MimePart;
use MxmUser\Exception\NotAuthorizedUserException;
use MxmRbac\Service\AuthorizationService;

use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use MxmUser\Service\UserServiceInterface;
use MxmUser\Service\UserService;
use MxmUser\Model\User;

/**
 *
 */
class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $userService;
    protected $paginator;
    protected $authService;
    protected $authorizationService;
    protected $datetime;
    protected $emailValidator;
    protected $notEmptyValidator;
    protected $isUserExists;
    protected $bcrypt;
    protected $mapper;

    protected $traceError = true;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        parent::setUp();

        $this->mapper = $this->prophesize(MapperInterface::class);
        $this->datetime = $this->prophesize(\DateTime::class);
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->emailValidator = $this->prophesize(EmailAddress::class);
        $this->notEmptyValidator = $this->prophesize(NotEmpty::class);
        $this->isUserExists = $this->prophesize(RecordExists::class);
        $this->authorizationService = $this->prophesize(AuthorizationService::class);
        $this->bcrypt = $this->prophesize(Bcrypt::class);

        $this->userService = new UserService(
            $this->mapper->reveal(),
            $this->datetime->reveal(),
            $this->authService->reveal(),
            $this->emailValidator->reveal(),
            $this->notEmptyValidator->reveal(),
            $this->isUserExists->reveal(),
            $this->authorizationService->reveal(),
            $this->bcrypt->reveal()
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testfindAllUsers()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('find.users')->willReturn(true);
        $array = array();
        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->mapper->findAllUsers()->willReturn($paginator);
        $this->assertSame($paginator, $this->userService->findAllUsers());
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testfindAllUsersByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->authorizationService->isGranted('find.users')->willReturn(true);
        $array = array();
        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->mapper->findAllUsers()->willReturn($paginator);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->findAllUsers();
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testfindAllUsersByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('find.users')->willReturn(false);
        $array = array();
        $paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->mapper->findAllUsers()->willReturn($paginator);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->findAllUsers();
    }



}

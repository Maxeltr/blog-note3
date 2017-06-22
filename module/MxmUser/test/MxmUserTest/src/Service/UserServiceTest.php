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

namespace MxmUserTest\Service;

use MxmUser\Mapper\MapperInterface;
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
use MxmUser\Exception\NotAuthorizedUserException;
use MxmRbac\Service\AuthorizationService;
use Prophecy\Argument;
use MxmUser\Service\UserService;
use MxmUser\Model\User;
use Zend\Authentication\Storage\StorageInterface;
use MxmUser\Service\Authentication\Adapter\AuthAdapter;
use Zend\Authentication\Result;
use MxmMail\Service\MailService;

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
    protected $user;
    protected $password;
    protected $email;
    protected $resultMock;
    protected $storageMock;
    protected $mail;
    protected $token;

    protected $traceError = true;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $array = array();
        $this->paginator = new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->user = new User();
        $this->user->setId('1');
        $this->email = 'testEditEmailMethod@test.ru';
        $this->user->setEmail('test@test.ru');
        $bcrypt = new Bcrypt();
        $this->password = 'testPassword';
        $passwordHash = $bcrypt->create($this->password);
        $this->user->setPassword($passwordHash);
        $this->token = 'dsg4tfsgf5gs';

        $this->mapper = $this->prophesize(MapperInterface::class);
        $this->datetime = $this->prophesize(\DateTime::class);
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->emailValidator = $this->prophesize(EmailAddress::class);
        $this->notEmptyValidator = $this->prophesize(NotEmpty::class);
        $this->isUserExists = $this->prophesize(RecordExists::class);
        $this->authorizationService = $this->prophesize(AuthorizationService::class);
        $this->bcrypt = $this->prophesize(Bcrypt::class);
        $this->resultMock = $this->prophesize(Result::class);
        $this->storageMock = $this->prophesize(StorageInterface::class);
        $this->mail = $this->prophesize(MailService::class);

        $this->userService = new UserService(
            $this->mapper->reveal(),
            $this->datetime->reveal(),
            $this->authService->reveal(),
            $this->emailValidator->reveal(),
            $this->notEmptyValidator->reveal(),
            $this->isUserExists->reveal(),
            $this->authorizationService->reveal(),
            $this->bcrypt->reveal(),
            $this->mail->reveal()
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
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testFindAllUsers()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('find.users')->willReturn(true);

        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->assertSame($this->paginator, $this->userService->findAllUsers());
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testFindAllUsersByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->authorizationService->isGranted('find.users')->willReturn(true);

        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->findAllUsers();
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testFindAllUsersByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('find.users')->willReturn(false);

        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->findAllUsers();
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserById()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willReturn($this->user);
        $this->authorizationService->isGranted('find.user', $this->user)->willReturn(true);
        $this->assertSame($this->user, $this->userService->findUserById('1'));
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->mapper->findUserById('1')->willReturn($this->user);
        $this->authorizationService->isGranted('find.user', $this->user)->willReturn(true);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdThrowsRecordNotFoundUserException()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willThrow(RecordNotFoundUserException::class);
        $this->authorizationService->isGranted('find.user', $this->user)->willReturn(true);
        $this->setExpectedException(RecordNotFoundUserException::class);
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willReturn($this->user);
        $this->authorizationService->isGranted('find.user', $this->user)->willReturn(false);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::insertUser
     *
     */
    public function testInsertUser()
    {
        $this->isUserExists->isValid($this->user->getEmail())->willReturn(false);
        $this->mapper->insertUser($this->user)->willReturn($this->user);
        $this->datetime->modify('now')->willReturn($this->datetime);
        $this->assertSame($this->user, $this->userService->insertUser($this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::insertUser
     *
     */
    public function testInsertUserAlreadyExsist()
    {
        $this->isUserExists->isValid($this->user->getEmail())->willReturn(true);
        $this->setExpectedException(AlreadyExistsUserException::class, 'User with email address ' . $this->user->getEmail() . ' already exists');
        $this->userService->insertUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.user', $this->user)->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->assertSame($this->user, $this->userService->updateUser($this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUserByIdByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->authorizationService->isGranted('edit.user', $this->user)->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->updateUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUserByIdByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.user', $this->user)->willReturn(false);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->updateUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('delete.user', $this->user)->willReturn(true);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->assertSame(true, $this->userService->deleteUser($this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUserByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->authorizationService->isGranted('delete.user', $this->user)->willReturn(true);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->deleteUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUserByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('delete.user', $this->user)->willReturn(false);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->deleteUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmail()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->userService->editEmail($this->email, $this->password);
        $this->assertSame($this->email, $this->user->getEmail());
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailByNotAuthenticatedUser()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->authorizationService->isGranted('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(NotAuthenticatedUserException::class, 'The user is not logged in');
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailByNotAuthorizationUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.email')->willReturn(false);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(NotAuthorizedUserException::class, 'Access denied');
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailEmptyPassword()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(false);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(InvalidArgumentUserException::class, 'No params given: password.');
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailInvalidEmail()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(false);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(InvalidArgumentUserException::class, 'No params given: email.');
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailInvalidPassword()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(false);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->setExpectedException(InvalidPasswordUserException::class, 'Incorrect password.');
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editPassword
     *
     */
    public function testEditPassword()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authorizationService->isGranted('edit.password')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);

        $bcrypt = new Bcrypt();
        $this->passwordHash = $bcrypt->create('newPassword');
        $this->bcrypt->create('newPassword')->willReturn($this->passwordHash);

        //$this->assertSame($this->user, $this->userService->editPassword($this->password, 'newPassword'));

        $this->userService->editPassword($this->password, 'newPassword');
        $this->password = 'newPassword';
        $this->assertSame($this->passwordHash, $this->user->getPassword());
    }

    /**
     * @covers MxmUser\Service\UserService::loginUser
     *
     */
    public function testLoginUser()
    {
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->hasIdentity()->willReturn(false);
        $this->resultMock->isValid()->willReturn(true);
        $this->authService->getAdapter()->willReturn(new AuthAdapter($this->mapper->reveal()));
        $result = $this->resultMock->reveal();
        $this->authService->authenticate()->willReturn($result);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $this->authService->getStorage()->willReturn($this->storageMock->reveal());
        $this->storageMock->write($this->user)->shouldBeCalled();
        $this->assertSame($result, $this->userService->loginUser($this->email, $this->password));
    }

    /**
     * @covers MxmUser\Service\UserService::loginUser
     *
     */
    public function testLoginUserEmptyPassword()
    {
        $this->notEmptyValidator->isValid($this->password)->willReturn(false);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->hasIdentity()->willReturn(false);
        $this->resultMock->isValid()->willReturn(true);
        $this->authService->getAdapter()->willReturn(new AuthAdapter($this->mapper->reveal()));
        $result = $this->resultMock->reveal();
        $this->authService->authenticate()->willReturn($result);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $this->authService->getStorage()->willReturn($this->storageMock->reveal());
        $this->setExpectedException(InvalidArgumentUserException::class, 'No params given: password.');
        $this->userService->loginUser($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::loginUser
     *
     */
    public function testLoginUserInvalidEmail()
    {
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(false);
        $this->authService->hasIdentity()->willReturn(false);
        $this->resultMock->isValid()->willReturn(true);
        $this->authService->getAdapter()->willReturn(new AuthAdapter($this->mapper->reveal()));
        $result = $this->resultMock->reveal();
        $this->authService->authenticate()->willReturn($result);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $this->authService->getStorage()->willReturn($this->storageMock->reveal());
        $this->setExpectedException(InvalidArgumentUserException::class, "No params given: email.");
        $this->userService->loginUser($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::loginUser
     *
     */
    public function testLoginUserByIdByAuthenticatedUser()
    {
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->hasIdentity()->willReturn(true);
        $this->resultMock->isValid()->willReturn(true);
        $this->authService->getAdapter()->willReturn(new AuthAdapter($this->mapper->reveal()));
        $result = $this->resultMock->reveal();
        $this->authService->authenticate()->willReturn($result);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $this->authService->getStorage()->willReturn($this->storageMock->reveal());
        $this->setExpectedException(RuntimeUserException::class, 'The user already logged in');
        $this->userService->loginUser($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::loginUser
     *
     */
    public function testLoginUserIdentityNotFound()
    {
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->hasIdentity()->willReturn(false);
        $this->resultMock->isValid()->willReturn(false);
        $this->authService->getAdapter()->willReturn(new AuthAdapter($this->mapper->reveal()));
        $result = $this->resultMock->reveal();
        $this->authService->authenticate()->willReturn($result);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $this->authService->getStorage()->willReturn($this->storageMock->reveal());
        $this->storageMock->write($this->user)->shouldNotBeCalled();
        $this->assertSame($result, $this->userService->loginUser($this->email, $this->password));
    }

    /**
     * @covers MxmUser\Service\UserService::logoutUser
     *
     */
    public function testLogoutUser()
    {
        $this->authService->hasIdentity()->willReturn(true);
        $this->authService->clearIdentity()->shouldBeCalled();
        $this->userService->logoutUser();
    }

    /**
     * @covers MxmUser\Service\UserService::logoutUser
     *
     */
    public function testLogoutUserNotLoggedIn()
    {
        $this->authService->hasIdentity()->willReturn(false);
        $this->setExpectedException(RuntimeUserException::class, 'The user is not logged in');
        $this->userService->logoutUser();
    }

    /**
     * @covers MxmUser\Service\UserService::resetPassword
     *
     */
    public function testResetPassword()
    {
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->mapper->findUserByEmail($this->email)->willReturn($this->user);
        $datetime = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->datetime->modify('now')->willReturn($datetime);
        $this->mapper->updateUser($this->user)->shouldBeCalled();
        $this->mail->sendEmail('Password Reset', Argument::any(), 'qwer_qwerty_2018@inbox.ru', 'blog-note3', $this->user->getEmail(), $this->user->getUsername())->shouldBeCalled();
        $token = $this->user->getPasswordToken();
        $this->userService->resetPassword($this->email);
        $this->assertNotSame($token, $this->user->getPasswordToken());
        $this->assertSame($datetime->format('Y-m-d H:i:s'), $this->user->getDateToken()->format('Y-m-d H:i:s'));
    }

    /**
     * @covers MxmUser\Service\UserService::resetPassword
     *
     */
    public function testResetPasswordInvalidEmail()
    {
        $this->emailValidator->isValid($this->email)->willReturn(false);
        $this->setExpectedException(InvalidArgumentUserException::class, "No params given: email.");
        $this->userService->resetPassword($this->email);
    }

    /**
     * @covers MxmUser\Service\UserService::resetPassword
     *
     */
    public function testResetPasswordNotFoundUser()
    {
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->mapper->findUserByEmail($this->email)->willThrow(\Exception::class);
        $this->setExpectedException(RecordNotFoundUserException::class, "User with email address " . $this->email . " doesn't exists");
        $this->userService->resetPassword($this->email);
    }

    /**
     * @covers MxmUser\Service\UserService::setPassword
     *
     */
    public function testSetPassword()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByResetPasswordToken($token)->willReturn($this->user);

        $tokenCreationDate = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->user->setDateToken($tokenCreationDate);
        $this->datetime->modify('now')->willReturn(new \DateTime('now', new \DateTimeZone('Europe/Moscow')));
        $this->bcrypt->create('newPassword')->willReturn('newPassword');
        $this->mapper->updateUser($this->user)->shouldBeCalled();
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $user = $this->userService->setPassword('newPassword', $token);
        $this->assertSame('newPassword', $user->getPassword());
    }

    /**
     * @covers MxmUser\Service\UserService::setPassword
     *
     */
    public function testSetPasswordEmptyNewPassword()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid('newPassword')->willReturn(false);
        $this->setExpectedException(InvalidArgumentUserException::class, 'No params given: password.');
        $this->userService->setPassword('newPassword', $token);
    }

    /**
     * @covers MxmUser\Service\UserService::setPassword
     *
     */
    public function testSetPasswordEmptyToken()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->notEmptyValidator->isValid($token)->willReturn(false);
        $this->setExpectedException(InvalidArgumentUserException::class, 'No params given: token.');
        $this->userService->setPassword('newPassword', $token);
    }

    /**
     * @covers MxmUser\Service\UserService::setPassword
     *
     */
    public function testSetPasswordTokenNotFound()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByResetPasswordToken($token)->willThrow(\Exception::class);
        $this->setExpectedException(RecordNotFoundUserException::class, "Token doesn't exists");
        $this->userService->setPassword('newPassword', $token);
    }

    /**
     * @covers MxmUser\Service\UserService::setPassword
     *
     */
    public function testSetPasswordExpiredToken()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByResetPasswordToken($token)->willReturn($this->user);
        $tokenCreationDate = new \DateTime('2017-01-01', new \DateTimeZone('Europe/Moscow'));
        $this->user->setDateToken($tokenCreationDate);
        $this->datetime->modify('now')->willReturn(new \DateTime('now', new \DateTimeZone('Europe/Moscow')));
        $this->setExpectedException(ExpiredUserException::class, "Token " . $token . " expired. User id " . $this->user->getId());
        $this->userService->setPassword('newPassword', $token);
    }
}

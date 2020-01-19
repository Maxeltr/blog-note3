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
//use Laminas\Authentication\AuthenticationService;
use MxmUser\Service\Authentication\AuthenticationService;
use Laminas\Validator\Db\RecordExists;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use MxmUser\Exception\RuntimeUserException;
use MxmUser\Exception\ExpiredUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use Laminas\Crypt\Password\Bcrypt;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;
use MxmRbac\Service\AuthorizationService;
use Prophecy\Argument;
use MxmUser\Service\UserService;
use MxmUser\Model\User;
use Laminas\Authentication\Storage\StorageInterface;
use MxmUser\Service\Authentication\Adapter\AuthAdapter;
use Laminas\Authentication\Result;
use MxmMail\Service\MailService;
use Laminas\Session\Container as SessionContainer;
use MxmUser\Validator\IsPropertyMatchesDb;
use Laminas\Http\PhpEnvironment\Request;
use Zend\i18n\Translator\TranslatorInterface;
use MxmRbac\Exception\NotAuthorizedException;

class UserServiceTest extends \PHPUnit\Framework\TestCase
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
    protected $sessionContainer;
    protected $translator;
    protected $request;
    protected $isRoleMatchesDb;
    protected $passwordHash;

    protected $traceError = true;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $array = array();
        $this->paginator = new \Laminas\Paginator\Paginator(
            new \Laminas\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->user = new User();
        $this->user->setId('1');
        $this->email = 'testEditEmailMethod@test.ru';
        $this->user->setEmail('test@test.ru');
        $bcrypt = new Bcrypt();
        $this->password = 'testPassword';
        $this->passwordHash = $bcrypt->create($this->password);
        $this->user->setPassword($this->passwordHash);
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

        $this->isRoleMatchesDb = $this->prophesize(IsPropertyMatchesDb::class);
        $this->request = $this->prophesize(Request::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->sessionContainer = $this->prophesize(SessionContainer::class);

        $this->userService = new UserService(
            $this->mapper->reveal(),
            $this->datetime->reveal(),
            $this->authService->reveal(),
            $this->emailValidator->reveal(),
            $this->notEmptyValidator->reveal(),
            $this->isUserExists->reveal(),
            $this->isRoleMatchesDb->reveal(),
            $this->authorizationService->reveal(),
            $this->bcrypt->reveal(),
            $this->mail->reveal(),
            $this->sessionContainer->reveal(),
            $this->translator->reveal(),
            $this->request->reveal()
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
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('find.users')->willReturn(true);

        $this->mapper->findAllUsers()->willReturn(clone $this->paginator);
        $this->assertEquals($this->paginator, $this->userService->findAllUsers());
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testFindAllUsersByNotAuthenticatedUser()
    {
        $this->authService->checkIdentity()->willThrow(NotAuthenticatedUserException::class);
        $this->authorizationService->checkPermission('find.users')->willReturn(true);

        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->expectException(NotAuthenticatedUserException::class);
        $this->userService->findAllUsers();
    }

    /**
     * @covers MxmUser\Service\UserService::findAllUsers
     *
     */
    public function testFindAllUsersByNotAuthorizationUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('find.users')->willThrow(NotAuthorizedException::class);

        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->expectException(NotAuthorizedException::class);
        $this->userService->findAllUsers();
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserById()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willReturn(clone $this->user);
        $this->authorizationService->checkPermission('find.user')->willReturn(true);
        $this->assertEquals($this->user, $this->userService->findUserById('1'));
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdByNotAuthenticatedUser()
    {
        $this->authService->checkIdentity()->willThrow(NotAuthenticatedUserException::class);
        $this->mapper->findUserById('1')->willReturn($this->user);
        $this->authorizationService->checkPermission('find.user', $this->user)->willReturn(true);
        $this->expectException(NotAuthenticatedUserException::class);
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdThrowsRecordNotFoundUserException()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willThrow(RecordNotFoundUserException::class);
        $this->authorizationService->checkPermission('find.user')->willReturn(true);
        $this->expectException(RecordNotFoundUserException::class);
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::findUserById
     *
     */
    public function testFindUserByIdByNotAuthorizationUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->mapper->findUserById('1')->willReturn($this->user);
        $this->authorizationService->checkPermission('find.user')->willThrow(NotAuthorizedException::class);
        $this->expectException(NotAuthorizedException::class);
        $this->userService->findUserById('1');
    }

    /**
     * @covers MxmUser\Service\UserService::insertUser
     *
     */
    public function testInsertUser()
    {
        $this->isUserExists->isValid($this->user->getEmail())->willReturn(false);

        $this->bcrypt->create($this->passwordHash)->willReturn($this->passwordHash);
        $this->mapper->findAllUsers()->willReturn($this->paginator);
        $this->mail->send()->willReturn($this->mail);
        $this->mail->setSubject(Argument::any())->willReturn($this->mail);
        $this->mail->setBody(Argument::any(), Argument::any())->willReturn($this->mail);
        $this->mail->setFrom(Argument::any(), Argument::any())->willReturn($this->mail);
        $this->mail->setTo(Argument::any(), Argument::any())->willReturn($this->mail);
        $this->datetime->modify('now')->willReturn($this->datetime);
        $this->user->setCreated($this->datetime->reveal());
        $this->user->setDateEmailToken($this->datetime->reveal());
        $this->mapper->insertUser($this->user)->willReturn($this->user);
        $this->assertEquals($this->user, $this->userService->insertUser($this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::insertUser
     *
     */
    public function testInsertUserAlreadyExsist()
    {
        $this->isUserExists->isValid($this->user->getEmail())->willReturn(true);
        $this->expectException(AlreadyExistsUserException::class);
        $this->userService->insertUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.user', $this->user)->willReturn(true);
        $this->authorizationService->checkPermission('change.role')->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn(clone $this->user);
        $this->assertEquals($this->user, $this->userService->updateUser(clone $this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUserByIdByNotAuthenticatedUser()
    {
        $this->authService->checkIdentity()->willThrow(NotAuthenticatedUserException::class);
        $this->authorizationService->checkPermission('edit.user', $this->user)->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(NotAuthenticatedUserException::class);
        $this->userService->updateUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::updateUser
     *
     */
    public function testUpdateUserByIdByNotAuthorizationUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.user', $this->user)->willThrow(NotAuthorizedException::class);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(NotAuthorizedException::class);
        $this->userService->updateUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('delete.user', $this->user)->willReturn(true);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->assertEquals(true, $this->userService->deleteUser(clone $this->user));
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUserByNotAuthenticatedUser()
    {
        $this->authService->checkIdentity()->willThrow(NotAuthenticatedUserException::class);
        $this->authorizationService->checkPermission('delete.user', $this->user)->willReturn(true);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->expectException(NotAuthenticatedUserException::class);
        $this->userService->deleteUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::deleteUser
     *
     */
    public function testDeleteUserByNotAuthorizationUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('delete.user', $this->user)->willThrow(NotAuthorizedException::class);
        $this->mapper->deleteUser($this->user)->willReturn(true);
        $this->expectException(NotAuthorizedException::class);
        $this->userService->deleteUser($this->user);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmail()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->userService->editEmail($this->email, $this->password);
        $this->assertEquals($this->email, $this->user->getEmail());
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailByNotAuthenticatedUser()
    {
        $this->authService->checkIdentity()->willThrow(NotAuthenticatedUserException::class);
        $this->authorizationService->checkPermission('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(NotAuthenticatedUserException::class);
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailByNotAuthorizationUser()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.email')->willThrow(NotAuthorizedException::class);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(NotAuthorizedException::class);
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailEmptyPassword()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(false);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(InvalidArgumentUserException::class);
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailInvalidEmail()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(false);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(InvalidArgumentUserException::class);
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editEmail
     *
     */
    public function testEditEmailInvalidPassword()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.email')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->emailValidator->isValid($this->email)->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(false);
        $this->mapper->updateUser($this->user)->willReturn($this->user);
        $this->expectException(InvalidPasswordUserException::class);
        $this->userService->editEmail($this->email, $this->password);
    }

    /**
     * @covers MxmUser\Service\UserService::editPassword
     *
     */
    public function testEditPassword()
    {
        $this->authService->checkIdentity()->willReturn(true);
        $this->authorizationService->checkPermission('edit.password')->willReturn(true);
        $this->notEmptyValidator->isValid($this->password)->willReturn(true);
        $this->notEmptyValidator->isValid('newPassword')->willReturn(true);
        $this->authService->getIdentity()->willReturn($this->user);
        $this->bcrypt->verify($this->password, $this->user->getPassword())->willReturn(true);
        $this->mapper->updateUser($this->user)->willReturn($this->user);

        $bcrypt = new Bcrypt();
        $this->passwordHash = $bcrypt->create('newPassword');
        $this->bcrypt->create('newPassword')->willReturn($this->passwordHash);
        $this->userService->editPassword($this->password, 'newPassword');
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
        $this->expectException(InvalidArgumentUserException::class);
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
        $this->expectException(InvalidArgumentUserException::class);
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
        $this->expectException(RuntimeUserException::class);
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
        $this->authService->checkIdentity()->willReturn(true);
        $this->authService->clearIdentity()->shouldBeCalled();
        $this->userService->logoutUser();
    }

    /**
     * @covers MxmUser\Service\UserService::logoutUser
     *
     */
    public function testLogoutUserNotLoggedIn()
    {
        $this->authService->checkIdentity()->willThrow(RuntimeUserException::class);;
        $this->expectException(RuntimeUserException::class);
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
        $this->mail->send()->willReturn($this->mail);
        $this->mail->setSubject(Argument::any())->willReturn($this->mail);
        $this->mail->setBody(Argument::any(), Argument::any())->willReturn($this->mail);
        $this->mail->setFrom(Argument::any(), Argument::any())->willReturn($this->mail);
        $this->mail->setTo(Argument::any(), Argument::any())->willReturn($this->mail);
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
        $this->expectException(InvalidArgumentUserException::class);
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
        $this->expectException(RecordNotFoundUserException::class);
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
        $this->expectException(InvalidArgumentUserException::class);
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
        $this->expectException(InvalidArgumentUserException::class);
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
        $this->expectException(RecordNotFoundUserException::class);
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
        $this->expectException(ExpiredUserException::class);
        $this->userService->setPassword('newPassword', $token);
    }

    /**
     * @covers MxmUser\Service\UserService::confirmEmail
     *
     */
    public function testconfirmEmail()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByEmailToken($token)->willReturn($this->user);
        $tokenCreationDate = new \DateTime('now', new \DateTimeZone('Europe/Moscow'));
        $this->user->setDateEmailToken($tokenCreationDate);
        $this->datetime->modify('now')->willReturn(new \DateTime('now', new \DateTimeZone('Europe/Moscow')));
	$this->mapper->updateUser($this->user)->shouldBeCalled();
        $this->mapper->updateUser($this->user)->willReturn($this->user);
	$user = $this->userService->confirmEmail($token);
        $this->assertSame(true, $user->getEmailVerification());
    }

    /**
     * @covers MxmUser\Service\UserService::confirmEmail
     *
     */
    public function testconfirmEmailEmptyToken()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid($token)->willReturn(false);
        $this->expectException(InvalidArgumentUserException::class);
        $this->userService->confirmEmail($token);
    }

    /**
     * @covers MxmUser\Service\UserService::confirmEmail
     *
     */
    public function testconfirmEmailTokenNotFound()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByEmailToken($token)->willThrow(\Exception::class);
        $this->expectException(RecordNotFoundUserException::class);
        $this->userService->confirmEmail($token);
    }

    /**
     * @covers MxmUser\Service\UserService::confirmEmail
     *
     */
    public function testconfirmEmailExpiredToken()
    {
        $token = 'dsg4tfsgf5gs';
        $this->notEmptyValidator->isValid($token)->willReturn(true);
        $this->mapper->findUserByEmailToken($token)->willReturn($this->user);
        $tokenCreationDate = new \DateTime('2017-01-01', new \DateTimeZone('Europe/Moscow'));
        $this->user->setDateEmailToken($tokenCreationDate);
        $this->datetime->modify('now')->willReturn(new \DateTime('now', new \DateTimeZone('Europe/Moscow')));
        $this->expectException(ExpiredUserException::class);
        $this->userService->confirmEmail($token);
    }
}

<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmUser\Service;

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
use MxmUser\Exception\NotAuthorizedUserException;
use MxmRbac\Service\AuthorizationService;
use MxmMail\Service\MailService;
use Zend\Math\Rand;

class UserService implements UserServiceInterface
{
    /**
     * @var \User\Mapper\MapperInterface;
     */
    protected $mapper;

    /**
     * @var DateTimeInterface;
     */
    protected $datetime;

    /**
     * @var Zend\Authentication\AuthenticationService;
     */
    protected $authService;

    /**
     * @var Zend\Validator\EmailAddress;
     */
    protected $emailValidator;

    /**
     * @var Zend\Validator\NotEmpty;
     */
    protected $notEmptyValidator;

    /**
     * @var Zend\Validator\Db\RecordExists;
     */
    protected $isUserExists;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Crypt\Password\Bcrypt
     */
    protected $bcrypt;

    /**
     * @var MxmMail\Service\MailService
     */
    protected $mail;

    public function __construct(
        MapperInterface $mapper,
        \DateTimeInterface $datetime,
        AuthenticationService $authService,
        EmailAddress $emailValidator,
        NotEmpty $notEmptyValidator,
        RecordExists $isUserExists,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt,
        MailService $mail
    ) {
        $this->mapper = $mapper;
        $this->datetime = $datetime;
        $this->authService = $authService;
        $this->emailValidator = $emailValidator;
        $this->notEmptyValidator = $notEmptyValidator;
        $this->isUserExists = $isUserExists;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
        $this->mail = $mail;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllUsers()
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('find.users')) {
            throw new NotAuthorizedUserException('Access denied');
        }

        return $this->mapper->findAllUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserById($id)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        $user = $this->mapper->findUserById($id);
        if ($user instanceof UserInterface) {
            if (!$this->authorizationService->isGranted('find.user', $user)) {
                throw new NotAuthorizedUserException('Access denied');
            }
        } else {
            throw new Exception\RuntimeException('mapper->findUserById returns value which does not implement UserInterface');
        }

	return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function insertUser(UserInterface $user)
    {
        if ($this->isUserExists->isValid($user->getEmail())) {
            throw new AlreadyExistsUserException("User with email address " . $user->getEmail() . " already exists");
        }

        $passwordHash = $this->bcrypt->create($user->getPassword());
        $user->setPassword($passwordHash);

        $user->setCreated($this->datetime->modify('now'));

        return $this->mapper->insertUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $user)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.user', $user)) {
            throw new NotAuthorizedUserException('Access denied');
        }

        return $this->mapper->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('delete.user', $user)) {
            throw new NotAuthorizedUserException('Access denied');
        }

        return $this->mapper->deleteUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function editEmail($email, $password)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.email')) {
            throw new NotAuthorizedUserException('Access denied');
        }

        if (!$this->notEmptyValidator->isValid($password)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }

        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        $currentUser = $this->authService->getIdentity();

        if (!$this->bcrypt->verify($password, $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect password.');
        }

        $currentUser->setEmail($email);

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function editPassword($oldPassword, $newPassword)
    {
        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('edit.password')) {
            throw new NotAuthorizedUserException('Access denied');
        }

        if (!$this->notEmptyValidator->isValid($oldPassword) or !$this->notEmptyValidator->isValid($newPassword)) {
            throw new InvalidArgumentUserException("No params given: oldPassword or newPassword.");
        }

        $currentUser = $this->authService->getIdentity();

        if (!$this->bcrypt->verify($oldPassword, $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect old password.');
        }

        $currentUser->setPassword($this->bcrypt->create($newPassword));

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function loginUser($email, $password)
    {
        if (!$this->notEmptyValidator->isValid($password)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }

        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        if ($this->authService->hasIdentity()) {
            throw new RuntimeUserException('The user already logged in');
        }

        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        if ($result->isValid()) {
            $user = $this->mapper->findUserByEmail($email);
            $storage = $this->authService->getStorage();
            $storage->write($user);
        }

        return $result;
    }

    public function logoutUser()
    {
        if (!$this->authService->hasIdentity()) {
            throw new RuntimeUserException('The user is not logged in');
        }
        $this->authService->clearIdentity();

        return $this;
    }

    public function resetPassword($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new InvalidArgumentUserException("No params given: email.");
        }

        try {
            $user = $this->mapper->findUserByEmail($email);
        } catch (\Exception $e) {
            throw new RecordNotFoundUserException("User with email address " . $email . " doesn't exists");
        }

        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setPasswordToken($token);
        $user->setDateToken($this->datetime->modify('now'));

        $this->mapper->updateUser($user);

        $subject = 'Password Reset';

        $httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        $passwordResetUrl = '<a href="' . 'http://' . $httpHost . '/set/password/' . $token . '">Reset password</a>';

        $body = "Please follow the link below to reset your password:\n";
        $body .= " $passwordResetUrl\n";
        $body .= " If you haven't asked to reset your password, please ignore this message.\n";

        $this->mail->sendEmail($subject, $body, 'qwer_qwerty_2018@inbox.ru', 'blog-note3', $user->getEmail(), $user->getUsername());

        return $this;
    }

    public function setPassword($newPassword, $token)
    {
        if (!$this->notEmptyValidator->isValid($newPassword)) {
            throw new InvalidArgumentUserException("No params given: password.");
        }
        if (!$this->notEmptyValidator->isValid($token)) {
            throw new InvalidArgumentUserException("No params given: token.");
        }

        try {
            $user = $this->mapper->findUserByResetPasswordToken($token);
        } catch (\Exception $e) {
            throw new RecordNotFoundUserException("Token doesn't exists");
        }

        $tokenCreationDate = $user->getDateToken();
        $currentDate = $this->datetime->modify('now');
        $interval = $tokenCreationDate->diff($currentDate);
        if ($interval->i > 1) {     //TODO срок годности токена вынести в настройки
            throw new ExpiredUserException("Token " . $token . " expired. User id " . $user->getId());
        }

        $passwordHash = $this->bcrypt->create($newPassword);
        $user->setPassword($passwordHash);

        return $this->mapper->updateUser($user);
    }
}
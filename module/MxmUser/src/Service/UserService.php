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
use MxmUser\Exception\RuntimeUserException;
use MxmUser\Exception\NotAuthenticatedUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use Zend\Crypt\Password\Bcrypt;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\AlreadyExistsUserException;
use MxmUser\Exception\InvalidPasswordUserException;

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

    public function __construct(
        MapperInterface $mapper,
        DateTimeInterface $datetime,
        AuthenticationService $authService
    ) {
        $this->mapper = $mapper;
        $this->datetime = $datetime;
        $this->authService = $authService;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllUsers()
    {
        return $this->mapper->findAllUsers();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserById($id)
    {
	return $this->mapper->findUserById($id);
    }

    /**
     * {@inheritDoc}
     */
    public function insertUser(UserInterface $user)
    {
        if ($this->IsUserExists($user)) {
            throw new AlreadyExistsUserException("User with email address " . $user->getEmail() . " already exists");
        }

        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($user->getPassword());
        $user->setPassword($passwordHash);

        $user->setCreated($this->datetime->modify('now'));

        return $this->mapper->insertUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $user)
    {
        return $this->mapper->updateUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $user)
    {
        return $this->mapper->deleteUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function changeEmail(array $data)
    {
        if (!array_key_exists('newEmail', $data) or !array_key_exists('password', $data)) {
            throw new InvalidArgumentUserException("No params given: email or password.");
        }

        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }
        $currentUser = $this->authService->getIdentity();

        $bcrypt = new Bcrypt();
        if (!$bcrypt->verify($data['password'], $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect password.');
        }

        $currentUser->setEmail($data['newEmail']);

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function changePassword(array $data)
    {
        if (array_key_exists('oldPassword', $data) or array_key_exists('newPassword', $data)) {
            throw new InvalidArgumentUserException("No params given: oldPassword or newPassword.");
        }

        if (!$this->authService->hasIdentity()) {
            throw new NotAuthenticatedUserException('The user is not logged in');
        }
        $currentUser = $this->authService->getIdentity();

        $bcrypt = new Bcrypt();
        if (!$bcrypt->verify($data['oldPassword'], $currentUser->getPassword())) {
            throw new InvalidPasswordUserException('Incorrect old password.');
        }

        $currentUser->setPassword($bcrypt->create($data['newPassword']));

        return $this->mapper->updateUser($currentUser);
    }

    /**
     * {@inheritDoc}
     */
    public function loginUser($email, $password)
    {
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
    }

    private function isUserExists($user)
    {
        try {
            $this->mapper->findUserByEmail($user->getEmail());  //TODO  заменить на валидатор?
        } catch (RecordNotFoundUserException $e) {

            return false;
        }

        return true;
    }
}
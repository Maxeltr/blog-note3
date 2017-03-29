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
use MxmUser\Exception\RuntimeException;

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
    public function changePassword(array $data)
    {
        \Zend\Debug\Debug::dump($data);
        die('UserService changePassword');
    }
    
    /**
     * {@inheritDoc}
     */
    public function loginUser($email, $password)
    {
        if ($this->authService->hasIdentity()) {
            throw new RuntimeException('Already logged in');
        }

        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     */
    public function changeEmail(array $data)
    {
        \Zend\Debug\Debug::dump($data);
        die('UserService changeEmail');
    }
}
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

namespace MxmUser\Service\Authentication\Adapter;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Result;
use Laminas\Crypt\Password\Bcrypt;
use MxmUser\Mapper\MapperInterface;
use MxmUser\Exception\RecordNotFoundUserException;

class AuthAdapter implements AdapterInterface
{
    /**
     * @var string User email
     */
    private $email;
    
    /**
     * @var string Password
     */
    private $password;
    
    /**
     * @var MxmUser\Mapper\MapperInterface
     */
    protected $mapper;
    
    /**
     * Sets mapper
     *
     * @return void
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Проверка подлинности юзера
     *
     * @return \Laminas\Authentication\Result
     * @throws \Laminas\Authentication\Adapter\Exception\ExceptionInterface
     */
    public function authenticate()
    {
        //проверить есть ли юзер в базе
        try {
            $user = $this->mapper->findUserByEmail($this->email);
        } catch (RecordNotFoundUserException $ex) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Invalid credentials.']);
        }
        
        //если есть проверить не просроченный ли
        
        //получить хэш пароля из юзера (из БД)
        $passwordHash = $user->getPassword();
        
        //проверить совпадает с введенным паролем из формы
        $bcrypt = new Bcrypt();
        if ($bcrypt->verify($this->password, $passwordHash)) {
            return new Result(Result::SUCCESS, $this->email, ['Authenticated successfully.']);    //если совпадает вернуть Result::SUCCESS       
        }
        
        //не сопадает вернуть FAILURE_CREDENTIAL_INVALID
        return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Invalid credentials.']);
    }
    
    /**
     * Sets user email.     
     */
    public function setEmail($email) 
    {
        $this->email = $email;        
    }
    
    /**
     * Sets password.     
     */
    public function setPassword($password) 
    {
        $this->password = $password;        
    }
}
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

namespace MxmUser\Mapper;

use MxmUser\Model\UserInterface;

interface MapperInterface
{
    /**
     * @param int|string $id
     *
     * @return UserInterface
     * @throw RecordNotFoundUserException
     * @throw InvalidArgumentUserException
     */
    public function findUserById($id);

        /**
     * @param string $email
     *
     * @return UserInterface
     * @throw RecordNotFoundUserException
     * @throw InvalidArgumentUserException
     */
    public function findUserByEmail($email);

    /**
     *
     * throw InvalidArgumentUserException
     *
     * @return Paginator
     */
    public function findAllUsers();

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     * @throw InvalidArgumentUserException
     * @throw DataBaseErrorUserException
     */
    public function insertUser(UserInterface $user);

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     * @throw InvalidArgumentUserException
     * @throw DataBaseErrorUserException
     */
    public function updateUser(UserInterface $user);

    /**
     * @param UserInterface $user
     * Должен удалить полученный объект, реализующий UserInterface и вернуть true (если удалено) или false (если неудача).
     *
     *
     * @return bool
     */
    public function deleteUser(UserInterface $user);

    /**
     * @param string $token
     *
     * @return UserInterface
	 * @throw RecordNotFoundUserException
     * @throw InvalidArgumentUserException
     */
    public function findUserByResetPasswordToken($token);

    /**
     * @param string $token
     *
     * @return UserInterface
	 * @throw RecordNotFoundUserException
     * @throw InvalidArgumentUserException
     */
	public function findUserByEmailToken($token);
}
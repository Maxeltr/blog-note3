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

namespace MxmUser\Model;

use \DateTimeInterface;
use \DateTimeZone;

interface UserInterface
{
    /**
     * Возвращает ID записи
     *
     * @return int ID
     */
    public function getId();

    /**
     * Устанавливает ID записи
     * @param int $id ID записи.
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Возвращает имя юзера
     *
     * @return $this
     */
    public function getUsername();

    /**
     * Устанавливает имя юзера
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username);

    /**
     * Возвращает почту юзера
     *
     * @return $this
     */
    public function getEmail();

    /**
     * Устанавливает почту юзера
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email);

    /**
     * Возвращает пароль юзера
     *
     * @return $this
     */
    public function getPassword();

    /**
     * Устанавливает пароль юзера
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password);

    /**
     * Возвращает роль юзера
     *
     * @return $this
     */
    public function getRole();

    /**
     * Устанавливает роль юзера
     * @param string $role
     *
     * @return $this
     */
    public function setRole($role);

    /**
     * Возвращает часовой пояс юзера
     *
     * @return $this
     */
    public function getTimebelt();

    /**
     * Устанавливает часовой пояс юзера
     * @param DateTimeZone $timezone
     *
     * @return $this
     */
    public function setTimebelt(DateTimeZone $timebelt);

    /**
     * Возвращает время создания юзера
     *
     * @return $this
     */
    public function getCreated();

    /**
     * Устанавливает время создания юзера
     * @param DateTimeInterface $created
     *
     * @return $this
     */
    public function setCreated(DateTimeInterface $created);

    /**
     * Возвращает токен для сброса пароля
     *
     * @return $this
     */
    public function getPasswordToken();

    /**
     * Устанавливает токен для сброса пароля
     * @param string $passwordToken
     *
     * @return $this
     */
    public function setPasswordToken($passwordToken);

    /**
     * Возвращает дату создания токена для сброса пароля
     *
     * @return $this
     */
    public function getDateToken();

    /**
     * Устанавливает дату создания токена для сброса пароля
     * @param DateTimeInterface $dateToken
     *
     * @return $this
     */
    public function setDateToken(DateTimeInterface $dateToken);
}
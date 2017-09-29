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

namespace MxmUserTest\Model;

use MxmUser\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $user;

    protected function setUp()
    {
        $this->data = [
            'id' => 1,
            'username' => 'TestUsername',
            'email' => 'Test@email.com',
            'password' => 'TestPassword',
            'role' => 'TestRole',
            'passwordToken' => 'TestToken',
            'created' => new \DateTimeImmutable('1900-01-01 00:00:00', new \DateTimeZone('Europe/Moscow')),
            'dateToken' => new \DateTimeImmutable('1900-01-01 00:00:00', new \DateTimeZone('Asia/Barnaul')),
            'timebelt' => new \DateTimeZone('Europe/Moscow'),
            'emailVerification' => true,		//add
            'emailToken' => 'emailTestToken',
            'dateEmailToken' => new \DateTimeImmutable('1900-01-01 02:02:02', new \DateTimeZone('Asia/Barnaul')),
        ];

        $this->user = new User();

        parent::setUp();
    }

    /**
     * @covers MxmUser\Model\User
     *
     */
    public function testInitialValuesAreNull()
    {
        $this->assertNull($this->user->getId());
        $this->assertNull($this->user->getUsername());
        $this->assertNull($this->user->getEmail());
        $this->assertNull($this->user->getPassword());
        $this->assertNull($this->user->getRole());
        $this->assertNull($this->user->getPasswordToken());
        $this->assertNull($this->user->getCreated());
        $this->assertNull($this->user->getDateToken());
        $this->assertNull($this->user->getTimebelt());
        $this->assertNull($this->user->getEmailVerification());
        $this->assertNull($this->user->getEmailToken());
        $this->assertNull($this->user->getDateEmailToken());
    }

    /**
     * @covers MxmUser\Model\User
     *
     */
    public function testPropertiesAreSetsCorrectly()
    {
        $this->user->setId($this->data['id']);
        $this->user->setUsername($this->data['username']);
        $this->user->setEmail($this->data['email']);
        $this->user->setPassword($this->data['password']);
        $this->user->setRole($this->data['role']);
        $this->user->setPasswordToken($this->data['passwordToken']);
        $this->user->setCreated($this->data['created']);
        $this->user->setDateToken($this->data['dateToken']);
        $this->user->setTimebelt($this->data['timebelt']);
        $this->user->setEmailVerification($this->data['emailVerification']);
	$this->user->setEmailToken($this->data['emailToken']);
	$this->user->setDateEmailToken($this->data['dateEmailToken']);

        $this->assertSame($this->data['id'], $this->user->getId());
        $this->assertSame($this->data['username'], $this->user->getUsername());
        $this->assertSame($this->data['email'], $this->user->getEmail());
        $this->assertSame($this->data['password'], $this->user->getPassword());
        $this->assertSame($this->data['role'], $this->user->getRole());
        $this->assertSame($this->data['passwordToken'], $this->user->getPasswordToken());
        $this->assertSame($this->data['created'], $this->user->getCreated());
        $this->assertSame($this->data['dateToken'], $this->user->getDateToken());
        $this->assertSame($this->data['timebelt'], $this->user->getTimebelt());
        $this->assertSame($this->data['emailVerification'], $this->user->getEmailVerification());
	$this->assertSame($this->data['emailToken'], $this->user->getEmailToken());
	$this->assertSame($this->data['dateEmailToken'], $this->user->getDateEmailToken());
    }
}
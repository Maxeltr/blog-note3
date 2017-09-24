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

namespace MxmUserTest\Hydrator\UserFormHydrator;

use MxmUser\Model\User;
use MxmUser\Hydrator\UserFormHydrator\UserHydrator;
use \DateTimeZone;

class UserHydratorTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $user;
    protected $hydrator;
    protected $data;

    protected function setUp()
    {
        $this->data = [
            'id' => 1,
            'username' => 'TestUsername',
            'email' => 'Test@email.com',
            'password' => 'TestPassword',
            'role' => 'TestRole',
            'passwordToken' => 'TestToken',
            'created' => 'TestCreated',
            'dateToken' => 'TestDateToken',
            'timebelt' => 'TestTimebelt',
            'emailVerification' => true,
            'emailToken' => 'testEmailToken',
            //'dateEmailToken' => new \DateTimeImmutable('1900-01-01 01:01:01', new \DateTimeZone('Europe/Moscow')),
        ];

        $this->user = new User();
        $this->hydrator = new UserHydrator();

        parent::setUp();
    }

    /**
     * @covers MxmUser\Hydrator\\UserFormHydrator\UserHydrator::hydrate
     *
     */
    public function testHydrate()
    {
        $result = $this->hydrator->hydrate($this->data, $this->user);
        $this->assertSame($this->data['id'], $result->getId());
        $this->assertSame($this->data['username'], $result->getUsername());
        $this->assertSame($this->data['email'], $result->getEmail());
        $this->assertSame($this->data['password'], $result->getPassword());
        $this->assertSame($this->data['role'], $result->getRole());
        $this->assertSame($this->data['passwordToken'], $result->getPasswordToken());
        $this->assertSame($this->data['emailToken'], $result->getEmailToken());
        $this->assertSame($this->data['emailVerification'], $result->getEmailVerification());
        $this->assertSame(null, $result->getCreated());
        $this->assertSame(null, $result->getDateToken());
        $this->assertSame(null, $result->getTimebelt());
        $this->assertSame(null, $result->getDateEmailToken());
    }

    /**
     * @covers MxmUser\Hydrator\UserFormHydrator\UserHydrator::hydrate
     */
    public function testHydrateNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->hydrate($this->data, $user);
        $this->assertSame($user, $result);
    }

    /**
     * @covers MxmUser\Hydrator\UserFormHydrator\UserHydrator::extract
     */
    public function testExtract()
    {
        $data = [
            'id' => 1,
            'username' => 'TestUsername',
            'email' => 'Test@email.com',
            'password' => 'TestPassword',
            'role' => 'TestRole',
            'passwordToken' => 'TestToken',
            'created' => new \DateTimeImmutable('1900-01-01 00:00:00', new \DateTimeZone('Europe/Moscow')),
            'dateToken' => new \DateTimeImmutable('1900-01-01 00:00:00', new \DateTimeZone('Asia/Barnaul')),
            'timebelt' => new DateTimeZone('Europe/Moscow'),
            'emailVerification' => true,
            'emailToken' => 'testEmailToken',
            'dateEmailToken' => new \DateTimeImmutable('1900-01-01 01:01:01', new \DateTimeZone('Europe/Moscow')),
        ];
        $this->user->setId('1');
        $this->user->setUsername('TestUsername');
        $this->user->setEmail('Test@email.com');
        $this->user->setPassword('TestPassword');
        $this->user->setRole('TestRole');
        $this->user->setPasswordToken('TestToken');
        $this->user->setCreated($data['created']);
        $this->user->setDateToken($data['dateToken']);
        $this->user->setTimebelt($data['timebelt']);
        $this->user->setEmailVerification($data['emailVerification']);
        $this->user->setEmailToken($data['emailToken']);
        $this->user->setDateEmailToken($data['dateEmailToken']);

        $result = $this->hydrator->extract($this->user);
        unset($data['created']);
        unset($data['dateToken']);
        unset($data['timebelt']);
        unset($data['dateEmailToken']);
        $this->assertSame($data, $result);

    }

    /**
     * @covers MxmUser\Hydrator\UserFormHydrator\UserHydrator::extract
     */
    public function testExtractNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->extract($user);
        $this->assertSame(array(), $result);
    }

}
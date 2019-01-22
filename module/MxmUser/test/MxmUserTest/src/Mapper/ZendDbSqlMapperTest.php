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

namespace MxmUserTest\Mapper;

use MxmUser\Mapper\ZendDbSqlMapper;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Hydrator\HydratorInterface;
use Zend\Config\Config;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Driver\Pdo\Statement;
use Zend\Db\Adapter\Driver\ResultInterface;
use MxmUser\Model\User;
use MxmUser\Model\UserInterface;
use Prophecy\Argument;
use Zend\Db\Adapter\Platform\Mysql;
use Zend\Db\Adapter\DriverInterface;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Sql\Sql;
use MxmUser\Exception\RecordNotFoundUserException;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use MxmUser\Exception\DataBaseErrorUserException;

class ZendDbSqlMapperTest extends \PHPUnit\Framework\TestCase
{
	protected $traceError = true;

	protected $config;
	protected $user;
	protected $datetime;
	protected $datezone;
	protected $adapter;
	protected $data;
	protected $stmt;
	protected $result;
	protected $hydrator;
	protected $mapper;
        protected $platform;
        protected $driver;
        protected $sql;

	/**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $configArray = ['dateTime' => [
                'dateTimeFormat' => 'Y-m-d H:i:s',
                'timezone' => 'Europe/Moscow',
                'defaultDate' => '1900-01-01 00:00:00'
            ]
	];

	$this->data = [
            'id' => '1',
            'username' => 'TestUsername',
            'email' => 'Test@email.com',
            'password' => 'TestPassword',
            'role' => 'TestRole',
            'passwordToken' => 'TestToken',
            'created' => '1917-10-25 00:00:00',
            'dateToken' => '1917-10-25 00:00:00',
            'timebelt' => 'Europe/Moscow'
        ];

        $this->config = new Config($configArray);

        $this->datetime = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $this->datezone = new \DateTimeZone('Asia/Barnaul');

        $this->user = new User();
        $this->user->setCreated($this->datetime);
        $this->user->setDateToken($this->datetime);
        $this->user->setTimebelt($this->datezone);

        $this->hydrator = $this->prophesize(HydratorInterface::class);
        $this->hydrator->hydrate(Argument::any(), $this->user)->willReturn($this->user);
        $this->hydrator->extract($this->user)->willReturn($this->data);

        $this->result = $this->prophesize(ResultInterface::class);
        $this->result->isQueryResult()->willReturn(true);
        $this->result->getAffectedRows()->willReturn(1);
        $this->result->current()->willReturn($this->data);
        $this->result->getGeneratedValue()->willReturn(2);

        $this->stmt = $this->prophesize(Statement::class);
        $this->stmt->execute()->willReturn($this->result->reveal());

	$this->sql = $this->prophesize(Sql::class);
        $this->sql->prepareStatementForSqlObject(Argument::any())->willReturn($this->stmt);

        $this->mapper = new ZendDbSqlMapper($this->sql->reveal(), $this->hydrator->reveal(), $this->user, $this->config);

        parent::setUp();
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::findUserById
     *
     */
    public function testFindUserById()
    {
	$this->assertSame($this->user, $this->mapper->findUserById(1));
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::findUserByEmail
     *
     */
    public function testFindUserByEmail()
    {
	$this->assertSame($this->user, $this->mapper->findUserByEmail('test@email.ru'));
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::findUserByResetPasswordToken
     *
     */
    public function testFindUserByResetPasswordToken()
    {
	$this->assertSame($this->user, $this->mapper->findUserByResetPasswordToken('tmaighgtyret546'));
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::createObject
     *
     */
    public function testNoResultInstanceReturned()
    {
        $this->stmt->execute()->willReturn([]);
        $this->expectException(RecordNotFoundUserException::class);
        $this->mapper->findUserById('1');
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::createObject
     *
     */
    public function testNoQueryResultReturned()
    {
        $this->result->isQueryResult()->willReturn(false);
        $this->expectException(RecordNotFoundUserException::class);
        $this->mapper->findUserById('1');
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::createObject
     *
     */
    public function testNoAffectedRows()
    {
        $this->result->getAffectedRows()->willReturn(0);
        $this->expectException(RecordNotFoundUserException::class);
        $this->mapper->findUserById('1');
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::findAllUsers
     *
     */
    public function testFindAllUsers()
    {
	$this->assertInstanceOf(Paginator::class, $this->mapper->findAllUsers());
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::insertUser
     *
     */
    public function testInsertUser()
    {
        $user = $this->mapper->insertUser($this->user);
	$this->assertSame($this->user, $user);
        $this->assertSame(2, $user->getId());
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::saveInDb
     *
     */
    public function testNoResultInstanceReturnedAfterInsertUserInDb()
    {
        $this->stmt->execute()->willReturn([]);
        $this->expectException(DataBaseErrorUserException::class);
        $this->mapper->insertUser($this->user);
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::saveInDb
     *
     */
    public function testNoGeneratedValueReturnedAfterInsertUserInDb()
    {
        $this->result->getGeneratedValue()->willReturn(null);
        $user = $this->mapper->insertUser($this->user);
        $this->assertSame($this->user, $user);
        $this->assertSame(null, $user->getId());
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::updateUser
     *
     */
    public function testUpdateUser()
    {
        $user = $this->mapper->updateUser($this->user);
	$this->assertSame($this->user, $user);
        $this->assertSame(2, $user->getId());
    }

    /**
     * @covers MxmUser\Mapper\ZendDbSqlMapper::deleteUser
     *
     */
    public function testDeleteUser()
    {
        $user = $this->mapper->deleteUser($this->user);
	$this->assertTrue( $user);
    }
}
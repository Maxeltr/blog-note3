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

namespace MxmUserTest\Hydrator\UserMapperHydrator;

use MxmUser\Model\User;
use MxmUser\Model\UserInterface;
use \DateTimeInterface;
use Zend\Hydrator\HydratorInterface;
use MxmUser\Hydrator\UserMapperHydrator\DatesHydrator;
use Zend\Validator\Date;
use Zend\Config\Config;
use MxmUser\Exception\InvalidArgumentUserException;

class DatesHydratorTest extends \PHPUnit\Framework\TestCase
{
    protected $traceError = true;

    protected $datetime;

    protected $dateValidator;

    protected $config;

    protected $user;

    protected $data;

    protected $hydrator;

    protected function setUp()
    {
        $this->data = [
            'created' => '2017-03-30 14:08:02',
            'dateToken' => '2017-04-30 14:08:00'
        ];
        $configArray = [
            'dateTime' => [
                'dateTimeFormat' => 'Y-m-d H:i:s',
                'defaultDate' => '1900-01-01 00:00:00'
            ]
        ];
        $config = new Config($configArray);

        $this->datetime = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        //$this->datetime = $this->prophesize(DateTimeInterface::class);
        $this->dateValidator = $this->prophesize(Date::class);
        //$this->config = $this->prophesize(Config::class);
        //$this->user = $this->prophesize(UserInterface::class);
        $this->user = new User();

        $this->hydrator = new DatesHydrator($this->datetime, $this->dateValidator->reveal(), $config);

        parent::setUp();
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrate()
    {
        $this->dateValidator->isValid($this->data['created'])->willReturn(true);
        $this->dateValidator->isValid($this->data['dateToken'])->willReturn(true);
        $result = $this->hydrator->hydrate($this->data, $this->user);
        $this->assertSame($this->data['created'], $result->getCreated()->format('Y-m-d H:i:s'));
        $this->assertSame($this->data['dateToken'], $result->getDateToken()->format('Y-m-d H:i:s'));
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrateInvalidCreated()
    {
        $this->dateValidator->isValid($this->data['created'])->willReturn(false);
        $this->dateValidator->isValid($this->data['dateToken'])->willReturn(true);
        $result = $this->hydrator->hydrate($this->data, $this->user);
        $this->assertSame(null, $result->getCreated());
        $this->assertSame($this->data['dateToken'], $result->getDateToken()->format('Y-m-d H:i:s'));
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrateInvalidDateToken()
    {
        $this->dateValidator->isValid($this->data['created'])->willReturn(true);
        $this->dateValidator->isValid($this->data['dateToken'])->willReturn(false);
        $result = $this->hydrator->hydrate($this->data, $this->user);
        $this->assertSame($this->data['created'], $result->getCreated()->format('Y-m-d H:i:s'));
        $this->assertSame(null, $result->getDateToken());
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrateNoCreated()
    {
        $this->dateValidator->isValid($this->data['created'])->willReturn(true);
        $this->dateValidator->isValid($this->data['dateToken'])->willReturn(true);
        $data = $this->data;
        unset($data['created']);
        $result = $this->hydrator->hydrate($data, $this->user);
        $this->assertSame(null, $result->getCreated());
        $this->assertSame($this->data['dateToken'], $result->getDateToken()->format('Y-m-d H:i:s'));
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrateNoDateToken()
    {
        $this->dateValidator->isValid($this->data['created'])->willReturn(true);
        $this->dateValidator->isValid($this->data['dateToken'])->willReturn(true);
        $data = $this->data;
        unset($data['dateToken']);
        $result = $this->hydrator->hydrate($data, $this->user);
        $this->assertSame($this->data['created'], $result->getCreated()->format('Y-m-d H:i:s'));
        $this->assertSame(null, $result->getDateToken());
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::hydrate
     */
    public function testHydrateNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->hydrate($this->data, $user);
        $this->assertSame($user, $result);
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::extract
     */
    public function testExtract()
    {
        $this->user->setCreated(new \DateTimeImmutable($this->data['created'], new \DateTimeZone('Europe/Moscow')));
        $this->user->setDateToken(new \DateTimeImmutable($this->data['dateToken'], new \DateTimeZone('Europe/Moscow')));
        $result = $this->hydrator->extract($this->user);
        $this->assertSame($this->data, $result);
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\DatesHydrator::extract
     */
    public function testExtractNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->extract($user);
        $this->assertSame(array(), $result);
    }
}
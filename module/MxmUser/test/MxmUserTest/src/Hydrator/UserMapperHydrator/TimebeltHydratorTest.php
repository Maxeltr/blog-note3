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
use MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator;
use \DateTimeZone;
use Zend\Validator\Date;
use Zend\Config\Config;

class TimebeltHydratorTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    protected $config;

    protected $user;

    protected $data;

    protected $configArray;

    protected function setUp()
    {
        $this->configArray = [
            'dateTime' => [
                'dateTimeFormat' => 'Y-m-d H:i:s',
                'timezone' => 'Europe/Moscow',
                'defaultDate' => '1900-01-01 00:00:00'
            ]
        ];
        $config = new Config($this->configArray);
        $this->hydrator = new TimebeltHydrator($config);
        $this->user = new User();
        $this->data = ['timebelt' => 'Asia/Barnaul'];
        $this->timezone = new DateTimeZone($this->data['timebelt']);

        parent::setUp();
    }

    /**
     * @covers MxmUser\Hydrator\\UserMapperHydrator\TimebeltHydrator::hydrate
     *
     */
    public function testHydrate()
    {
        $result = $this->hydrator->hydrate($this->data, $this->user);
        $this->assertSame($this->data['timebelt'], $result->getTimebelt()->getName());
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator::hydrate
     */
    public function testHydrateNoTimebelt()
    {
        $data = [];
        $result = $this->hydrator->hydrate($data, $this->user);
        $this->assertSame(null, $result->getTimebelt());
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator::hydrate
     */
    public function testHydrateEmptyTimebelt()
    {
        $data = ['timebelt' => ''];
        $result = $this->hydrator->hydrate($data, $this->user);
        $this->assertSame(null, $result->getTimebelt());
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator::hydrate
     */
    public function testHydrateNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->hydrate($this->data, $user);
        $this->assertSame($user, $result);
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator::extract
     */
    public function testExtract()
    {
        $this->user->setTimebelt($this->timezone);
        $result = $this->hydrator->extract($this->user);
        $this->assertSame($this->data, $result);
    }

    /**
     * @covers MxmUser\Hydrator\UserMapperHydrator\TimebeltHydrator::extract
     */
    public function testExtractNotInstanceOfUserInterface()
    {
        $user = 'user';
        $result = $this->hydrator->extract($user);
        $this->assertSame(array(), $result);
    }
}
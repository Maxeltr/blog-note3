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


    protected function setUp()
    {
        $this->hydrator = new TimebeltHydrator();

        $this->data = ['timebelt' => '1'];
        $this->timezonesList = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, 'RU');
        $this->timezone = new DateTimeZone($this->timezonesList[$this->data['timebelt']]);
        $this->timezoneName = $this->timezone->getName();

        parent::setUp();
    }

    /**
     * @covers MxmUser\Hydrator\\UserMapperHydrator\TimebeltHydrator::hydrate
     *
     */
    public function testHydrate()
    {
        $result = $this->hydrator->hydrate($this->data, $this->timezone);
        $this->assertSame($this->timezoneName, $result->getName());
    }

}
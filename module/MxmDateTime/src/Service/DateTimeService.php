<?php

/*
 * The MIT License
 *
 * Copyright 2019 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmDateTime\Service;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Zend\Log\LoggerInterface;
use Zend\Config\Config;
use MxmDatetime\Exception\InvalidArgumentException;

class DateTimeService
{
    /**
     * @var \DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     * @var Zend\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $timezones;

    public function __construct(
        DateTimeInterface $datetime,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->datetime = $datetime;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get list of timezones with offsets
     *
     * @return array
     */
    public function getTimezoneListWithGmtOffsets()
    {
        $timezones = [];
        $offsets = [];

        $now = new DateTime('now', new DateTimeZone('UTC'));

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $now->setTimezone(new DateTimeZone($timezone));
            $offsets[] = $offset = $now->getOffset();
            $timezones[$timezone] = '(' . $this->format_GMT_offset($offset) . ') ' . $this->format_timezone_name($timezone);
        }

        array_multisort($offsets, $timezones);

        return $timezones;
    }

    protected function format_GMT_offset($offset)
    {
        $hours = intval($offset / 3600);                        //переводим из секунд в часы, отбрасываем дробную часть
        $minutes = abs(intval($offset % 3600 / 60));        //остаток от "перевода в часы" переводим в минуты, отбрасываем дроную часть, зачем-то берем модуль?
        return 'GMT' . ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');    //+03d - вывести число, указывать + для положительного числа, ширина = 3, дополнить нулями.
    }

    protected function format_timezone_name($name)
    {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('St ', 'St. ', $name);
        return $name;
    }

    /**
     * Retrieve a default timezone in config and return 'UTC' if there is no element set.
     *
     * @return string
     */
    public function getDefaultTimezone()
    {
        return $this->config->defaults->timezone ?: 'UTC';
    }

    /**
     * Retrieve a default DateTimeFormat in config and return 'Y-m-d H:i:s' if there is no element set.
     *
     * @return string
     */
    public function getDefaultDateTimeFormat()
    {
        return $this->config->defaults->dateTimeFormat ?: 'Y-m-d H:i:s';
    }
}

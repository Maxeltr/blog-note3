<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmFile\Hydrator\Strategy;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DateTime;
use Laminas\Hydrator\Strategy\StrategyInterface;

class DateTimeFormatterStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    private $format;

    /**
     * @var DateTimeZone|null
     */
    private $timezone;

    /**
     * Constructor
     *
     * @param string            $format
     * @param DateTimeZone|null $timezone
     */
    public function __construct($format = DateTime::RFC3339, DateTimeZone $timezone = null)
    {
        $this->format   = (string) $format;
        $this->timezone = $timezone;
    }

    /**
     * Converts to date time string
     *
     * @param mixed|DateTimeInterface $value
     *
     * @return mixed|string
     */
    public function extract($value, ?object $object = null)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format($this->format);
        }

        return $value;
    }

    /**
     * Converts date time string to DateTimeImmutable instance for injecting to object
     *
     * @param mixed|string $value
     *
     * @return mixed|DateTimeImmutable
     */
    public function hydrate($value, ?array $data)
    {
        if ($value === '' || $value === null) {
            return;
        }

        if ($this->timezone) {
            $hydrated = DateTimeImmutable::createFromFormat($this->format, $value, $this->timezone);
        } else {
            $hydrated = DateTimeImmutable::createFromFormat($this->format, $value);
        }

        return $hydrated ?: $value;
    }
}

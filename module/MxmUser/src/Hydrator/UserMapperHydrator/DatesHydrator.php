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

namespace MxmUser\Hydrator\UserMapperHydrator;

use MxmUser\Model\UserInterface;
use \DateTimeInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Validator\Date;
use Zend\Config\Config;

class DatesHydrator implements HydratorInterface
{
    private $datetime;

    private $dateValidator;

    private $config;

    public function __construct(\DateTimeInterface $datetime, Date $dateValidator, Config $config)
    {
        $this->dateValidator = $dateValidator;
        $this->datetime = $datetime;
        $this->config = $config;
    }

    public function hydrate(array $data, $object)
    {
        if (!$object instanceof UserInterface) {
            return $object;
        }

        if (array_key_exists('created', $data) && $this->dateValidator->isValid($data['created'])) {
            $object->setCreated($this->datetime->modify($data['created']));
        }
        if (array_key_exists('dateToken', $data) && $this->dateValidator->isValid($data['dateToken'])) {
            $object->setDateToken($this->datetime->modify($data['dateToken']));
        }

        return $object;
    }

    public function extract($object)
    {
        if (!$object instanceof UserInterface) {
            return array();
        }

        $values = [];

        $datetimeCreated = $object->getCreated();
        if ($datetimeCreated instanceof DateTimeInterface) {
            $values ['created'] = $datetimeCreated->format($this->config->dateTime->dateTimeFormat);
        }
        $datetimeDateToken = $object->getDateToken();
        if ($datetimeDateToken instanceof DateTimeInterface) {
            $values ['dateToken'] = $datetimeDateToken->format($this->config->dateTime->dateTimeFormat);
        }

        return $values;
    }
}
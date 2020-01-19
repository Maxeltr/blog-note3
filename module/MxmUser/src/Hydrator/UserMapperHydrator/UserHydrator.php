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
use Laminas\Hydrator\HydratorInterface;

class UserHydrator implements HydratorInterface
{
    /**
     * Properties to skip. Should be lowercase.
     *
     * @var array
     */
    protected $skipProperties = [
        'datetoken',
        'created',
        'timebelt',
        'dateemailtoken',
        '__construct'
    ];

    public function __construct()
    {
    }

    public function hydrate(array $data, $object)
    {
        if (!$object instanceof UserInterface) {
            return $object;
        }

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $this->skipProperties)) {
                continue;
            }

            $method = 'set' . $key;
            if(is_callable([$object, $method])) {
                $object->$method($value);
            }
        }

        return $object;
    }

    public function extract($object) : array
    {
        if (!$object instanceof UserInterface) {
            return array();
        }

        $methods = get_class_methods($object);
        $values = [];

        foreach ($methods as $method) {

            if (strpos($method, 'get') === 0) {
                $attribute = substr($method, 3);

                if (in_array(strtolower($attribute), $this->skipProperties)) {
                    continue;
                }

                if (!property_exists($object, $attribute)) {
                    $attribute = lcfirst($attribute);
                }
                $values[$attribute] = $object->$method();

            }
        }

        return $values;
    }
}
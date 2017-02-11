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

namespace MxmBlog\Hydrator\Tag;

use MxmBlog\Model\TagInterface;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;

class TagHydrator extends ClassMethods implements HydratorInterface
{
    public function __construct()
    {
        parent::__construct(false);
    }
    
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof TagInterface) {
            return $object;
        }
        
        if (array_key_exists('id', $data) && array_key_exists('title', $data) && array_key_exists('weight', $data)) {
            $object->setId(!empty($data['id']) ? $data['id'] : null);
            $object->setTitle(!empty($data['title']) ? $data['title'] : '');
            $object->setWeight(!empty($data['weight']) ? $data['weight'] : 0);
        }
        
        return $object;
    }

    public function extract($object)
    {
        if (!$object instanceof TagInterface) {
            return array();
        }

        return parent::extract($object);
    }
}
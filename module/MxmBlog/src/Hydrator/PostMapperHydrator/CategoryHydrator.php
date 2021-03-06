<?php

/* 
 * The MIT License
 *
 * Copyright 2016 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmBlog\Hydrator\PostMapperHydrator;

use MxmBlog\Model\PostInterface;
use MxmBlog\Model\CategoryInterface;
use Laminas\Hydrator\ClassMethodsHydrator as ClassMethods;
use Laminas\Hydrator\HydratorInterface;

class CategoryHydrator extends ClassMethods implements HydratorInterface
{
    private $category;
    
    public function __construct(CategoryInterface $category)
    {
        $this->category = $category;
        parent::__construct(false);
    }
    
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof PostInterface) {
            return $object;
        }

        $category = array();
        
        if (array_key_exists('categoryId', $data)) {
            $category['id'] = !empty($data['categoryId']) ? $data['categoryId'] : null;
        }
        if (array_key_exists('categoryTitle', $data)) {
            $category['title'] = !empty($data['categoryTitle']) ? $data['categoryTitle'] : null;
        }
        if (array_key_exists('categoryDescription', $data)) {
            $category['description'] = !empty($data['categoryDescription']) ? $data['categoryDescription'] : null;
        }
        
        $categoryClone = clone $this->category;
        parent::hydrate($category, $categoryClone);
        $object->setCategory($categoryClone);

        return $object;
    }

    public function extract($object) : array
    {
        if (!$object instanceof PostInterface) {
            return array();
        }

        $categoryObject = $object->getCategory();
        if ($categoryObject instanceof CategoryInterface) {
            return array('category' => parent::extract($categoryObject));
        }

        return array();
    }
}

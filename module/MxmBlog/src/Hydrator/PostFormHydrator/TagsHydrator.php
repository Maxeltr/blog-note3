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

namespace MxmBlog\Hydrator\PostFormHydrator;

use MxmBlog\Model\PostInterface;
use MxmBlog\Model\TagInterface;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Laminas\Hydrator\ClassMethodsHydrator as ClassMethods;
use Laminas\Tag\ItemList;
use Laminas\Hydrator\HydratorInterface;

class TagsHydrator extends ClassMethods implements HydratorInterface
{
    private $itemList;

    private $item;

    public function __construct(TagInterface $item, ItemList $itemList)
    {
        $this->item = $item;
        $this->itemList = $itemList;
        parent::__construct(false);
    }

    public function hydrate(array $data, $object)
    {
        if (!$object instanceof PostInterface) {
            return $object;
        }

        //В $data['tags'] уже гидрированный массив объектов TagInterface?? (гидрирован гидратором
        //ClassMethods, переданным в форму фабрикой?).
        if (array_key_exists('tags', $data) && is_array($data['tags'])) {
            for($i=0, $countTags = count($data['tags']); $i < $countTags; $i++) {
                if ($data['tags'][$i] instanceof TagInterface) {
                    $this->itemList->offsetSet($i, $data['tags'][$i]);
                }
            }

        } else {
            throw new InvalidArgumentBlogException("TagsHydrator. hydrate. Invalid params given.");
        }

        $object->setTags($this->itemList);

        return $object;
    }

    public function extract($object) : array
    {
        if (!$object instanceof PostInterface) {
            return array();
        }

        $itemList = $object->getTags();
        if(!$itemList instanceof ItemList) {
            return array();
        }

        $items = array();
        foreach($itemList as $item) {
            if($item instanceof TagInterface) {
                $items[] = $item;
            }
        }

        return array('tags' => $items);
    }
}
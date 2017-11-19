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
use MxmBlog\Model\TagInterface;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Zend\Hydrator\ClassMethods;
use Zend\Tag\ItemList;
use Zend\Hydrator\HydratorInterface;

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

        if (array_key_exists('tagIds', $data) && array_key_exists('tagTitles', $data) && array_key_exists('tagWeights', $data)) {
            $itemListClone = clone $this->itemList;

            $tagIds = explode(",", $data['tagIds']);
            $tagTitles = explode(",", $data['tagTitles']);
            $tagWeights = explode(",", $data['tagWeights']);

            for($i=0, $countTagIds = count($tagIds); $i < $countTagIds; $i++) {
                $tagClone = clone $this->item;
                $tagClone->setId(!empty($tagIds[$i]) ? $tagIds[$i] : null);
                $tagClone->setTitle(!empty($tagTitles[$i]) ? $tagTitles[$i] : '');
                $tagClone->setWeight(!empty($tagWeights[$i]) ? $tagWeights[$i] : 0);

                $itemListClone->offsetSet($i, $tagClone);
            }

        } else {
            throw new InvalidArgumentBlogException("TagsHydrator. hydrate. Invalid params given.");
        }

        $object->setTags($itemListClone);

        return $object;
    }

    public function extract($object)
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
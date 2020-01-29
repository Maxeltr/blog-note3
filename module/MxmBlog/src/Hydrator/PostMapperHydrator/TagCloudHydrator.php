<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\Hydrator\HydratorInterface;
use MxmBlog\Model\TagRepositoryInterface;
use MxmUser\Exception\RecordNotFoundUserException;
use Laminas\Tag\Cloud;
use Laminas\Tag\ItemList;
use MxmBlog\Model\TagInterface;
use Laminas\Paginator\Paginator;

class TagCloudHydrator extends ReflectionHydrator implements HydratorInterface {

    private $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository) {
        $this->tagRepository = $tagRepository;
    }

    public function hydrate(array $data, $object) {
        if (!$object instanceof PostInterface) {
            return $object;
        }

        try {
            $tags = $this->tagRepository->findTagsByPostId($data['id']);
        } catch (RecordNotFoundUserException $ex) {                         //TODO add logger
            return $object;
        }

        if ($tags instanceof Paginator) {
            $tags = iterator_to_array($tags->setItemCountPerPage(-1));
        }

        $cloud = new Cloud();
        $cloud->setTags($tags);
        $object->setTags($cloud->getItemList());

        return $object;
    }

    public function extract($object): array {
        $items = [];

        if (!$object instanceof PostInterface) {
            return $items;
        }

//        $cloud = $object->getTags();
//        if (! $cloud instanceof Cloud) {
//            return $items;
//        }
//        $itemList = $cloud->getItemList();
        $itemList = $object->getTags();
        if (!$itemList instanceof ItemList) {
            return $items;
        }

        foreach ($itemList as $item) {
            if ($item instanceof TagInterface) {
                $items[] = $item;
            }
        }

        return array('tags' => $items);
    }

}

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

namespace MxmBlog\Model;

use ArrayAccess;
use Laminas\Db\TableGateway\TableGateway;
use MxmBlog\Exception\DataBaseErrorBlogException;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Laminas\Validator\StaticValidator;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\ValidatorInterface;
use Laminas\Filter\StaticFilter;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Tag\ItemList;

class TagManager implements TagManagerInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagPostTableGateway;

    /**
     * @param TableGateway $tagTableGateway
     * @param TableGateway $tagPostTableGateway
     */
    public function __construct(
            TableGateway $tagTableGateway,
            TableGateway $tagPostTableGateway
    ) {
        $this->tagTableGateway = $tagTableGateway;
        $this->tagPostTableGateway = $tagPostTableGateway;
    }

    /**
     * {@see TagManagerInterface}
     */
    public function insertTag(TagInterface $tag) {
        $tagHydrator = $this->tagTableGateway->getResultSetPrototype()->getHydrator();
        $tagArray = $tagHydrator->extract($tag);
        unset($tagArray['id']);
        unset($tagArray['weight']);
        unset($tagArray['params']);
        unset($tagArray['skipOptions']);

        $this->tagTableGateway->insert($tagArray);
        $newId = $this->tagTableGateway->getLastInsertValue();
        if (!$newId) {
            throw new DataBaseErrorBlogException("Insert operation failed");
        }

        $resultSet = $this->tagTableGateway->select(['id' => $newId]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@see TagManagerInterface}
     */
    public function updateTag(TagInterface $tag) {
        $tagHydrator = $this->tagTableGateway->getResultSetPrototype()->getHydrator();
        $tagArray = $tagHydrator->extract($tag);
        unset($tagArray['id']);
        unset($tagArray['weight']);
        unset($tagArray['params']);
        unset($tagArray['skipOptions']);

        $this->tagTableGateway->update($tagArray, ['id' => $tag->getId()]);

        $resultSet = $this->tagTableGateway->select(['id' => $tag->getId()]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@see TagManagerInterface}
     */
    public function deleteTag(TagInterface $tag) {
        $this->deleteTagAssociationWithPosts($tag->getId());
        return $this->tagTableGateway->delete(['id' => $tag->getId()]);
    }

    /**
     * {@see TagManagerInterface}
     */
    public function deleteTags($tags) {
        if ($tags instanceof Paginator) {
            $tags = iterator_to_array($tags->setItemCountPerPage(-1));
        }

        if (!is_array($tags)) {
            throw new InvalidArgumentBlogException(sprintf(
                            'The data must be array; received "%s"',
                            (is_object($tags) ? get_class($tags) : gettype($tags))
            ));
        }

        if (empty($tags)) {

            return 0;
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof TagInterface) {
                return $value->getId();
            } else {
                throw new InvalidArgumentBlogException(sprintf(
                                'Invalid value in data array detected, value must be a string or instance of TagInterface, %s given.',
                                (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $tagIds = array_map($func, $tags);

        $sql = $this->tagTableGateway->getSql();
        $delete = $sql->delete();
        $delete->where->in('id', $tagIds);
        $result = $this->tagTableGateway->deleteWith($delete);
        if (0 !== $result) {
            $this->deleteTagAssociationsWithPosts($tagIds);
        }

        return $result;
    }

    /**
     * Удаляет связи тега с постами (article_id - tag_id)
     * @param string $id id тега
     *
     * @return bool
     */
    private function deleteTagAssociationWithPosts($id) {
        return $this->tagPostTableGateway->delete(['tag_id' => $id]);
    }

    /**
     * Удаляет связи тегов с тегами (article_id - tag_id)
     * @param array $ids
     *
     * @return Affected Rows
     */
    private function deleteTagAssociationsWithPosts($ids) {
        $sql = $this->tagPostTableGateway->getSql();
        $delete = $sql->delete();
        $delete->where->in('tag_id', $ids);

        return $this->tagPostTableGateway->deleteWith($delete);
    }

}

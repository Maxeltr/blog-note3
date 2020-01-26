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

use Laminas\Db\TableGateway\TableGateway;
use MxmBlog\Exception\RecordNotFoundBlogException;
use Laminas\Db\Sql\Predicate\Expression;

class TagRepository implements TagRepositoryInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @param TableGateway $tableGateway
     */
    public function __construct(
            TableGateway $tableGateway
    ) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * {@see TagRepositoryInterface}
     */
    public function findTagById($id) {
        $select = $this->tableGateway->getSql()->select();
        $select->join('articles_tags', 'tags.id = articles_tags.tag_id', ['tag_id'], 'left');
        $select->where(array('tags.id = ?' => $id));
        $select->columns([
            'id' => new Expression('tags.id'),
            'title' => new Expression('tags.title'),
            'weight' => new Expression('COUNT(articles_tags.tag_id)'),
        ]);
        $resultSet = $this->tableGateway->selectWith($select);

        if (0 === count($resultSet)) {
            throw new RecordNotFoundBlogException('Tag ' . $id . ' not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@see TagRepositoryInterface}
     */
    public function findTagsByPostId($id) {

    }
}

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
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Paginator\Adapter\DbSelect;

class TagRepository implements TagRepositoryInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagPostTableGateway;

    /**
     * @param TableGateway $tableGateway
     */
    public function __construct(
            TableGateway $tagTableGateway,
            TableGateway $tagPostTableGateway
    ) {
        $this->tagTableGateway = $tagTableGateway;
        $this->tagPostTableGateway = $tagPostTableGateway;
    }

    /**
     * {@see TagRepositoryInterface}
     */
    public function findTagById($id) {
        $select = $this->tagTableGateway->getSql()->select();
        $select->join('articles_tags', 'tags.id = articles_tags.tag_id', ['tag_id'], $select::JOIN_LEFT);
        $select->where(array('tags.id = ?' => $id));
        $select->columns([
            'id' => new Expression('tags.id'),
            'title' => new Expression('tags.title'),
            'weight' => new Expression('COUNT(articles_tags.tag_id)'),
        ]);
        $resultSet = $this->tagTableGateway->selectWith($select);

        if (0 === count($resultSet)) {
            throw new RecordNotFoundBlogException('Tag ' . $id . ' not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@see TagRepositoryInterface}
     */
    public function findTagsByPostId($id) {
        $sql = $this->tagTableGateway->getSql();
        $select = $sql->select();
        $select->order('id DESC');

        $select->join(
                'articles_tags',
                'tags.id = articles_tags.tag_id',
                [],
                $select::JOIN_LEFT
        );
        $select->where(['articles_tags.article_id = ?' => $id]);
        $select->group('tags.id');

        $subSelect = $this->tagPostTableGateway->getSql()->select();
        $subSelect->columns([
            'tag_id',
            'tag_weight' => new Expression('COUNT(`tag_id`)')
        ]);
        $subSelect->group('tag_id');

        $select->join(
                ['tag_weights' => $subSelect],
                'tags.id = tag_weights.tag_id',
                [],
                $select::JOIN_LEFT
        );

        $select->columns([
            'id' => new Expression('tags.id'),
            'title' => new Expression('MIN(tags.title)'),
            'weight' => new Expression('MIN(tag_weights.tag_weight)'),
        ]);

        $resultSetPrototype = $this->tagTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    public function findAllTags() {
        $sql = $this->tagTableGateway->getSql();
        $select = $sql->select();
        $select->group('tags.id');
        $select->order('id DESC');

        $subSelect = $this->tagPostTableGateway->getSql()->select();
        $subSelect->columns([
            'tag_id',
            'tag_weight' => new Expression('COUNT(`tag_id`)')
        ]);
        $subSelect->group('tag_id');

        $select->join(
                ['tag_weights' => $subSelect],
                'tags.id = tag_weights.tag_id',
                [],
                $select::JOIN_LEFT
        );

        $select->columns([
            'id' => new Expression('tags.id'),
            'title' => new Expression('MIN(tags.title)'),
            'weight' => new Expression('MIN(tag_weights.tag_weight)'),
        ]);

        $resultSetPrototype = $this->tagTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

}

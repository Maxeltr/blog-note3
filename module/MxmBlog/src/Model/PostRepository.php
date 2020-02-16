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
use MxmBlog\Exception\InvalidArgumentBlogException;
use Laminas\Validator\StaticValidator;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Sql\Predicate\Expression;

class PostRepository implements PostRepositoryInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $postTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagPostTableGateway;

    /**
     * @param TableGateway $postTableGateway
     * @param TableGateway $tagPostTableGateway
     */
    public function __construct(
            TableGateway $postTableGateway,
            TableGateway $tagPostTableGateway
    ) {
        $this->postTableGateway = $postTableGateway;
        $this->tagPostTableGateway = $tagPostTableGateway;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPostById($id, $hideUnpublished = true) {
        $select = $this->postTableGateway->getSql()->select();
        $select->where(['id' => $id]);
        if ($hideUnpublished === true) {
            $select->where(['isPublished' => true]);
        }
        $resultSet = $this->postTableGateway->selectWith($select);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundBlogException('Post ' . $id . ' not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findAllPosts($hideUnpublished = true) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();
        if ($hideUnpublished) {
            $select->where(['isPublished' => true]);
        }
        $select->order('id DESC');

        $resultSetPrototype = $this->postTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPostsByCategoryId($id, $hideUnpublished = true) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();
        if ($hideUnpublished) {
            $select->where(['isPublished' => true]);
        }
        $select->where(['categoryId' => $id]);
        $select->order('articles.id DESC');

        $resultSetPrototype = $this->postTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPostsByTagId($id, $hideUnpublished = true) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();
        if ($hideUnpublished) {
            $select->where(['isPublished' => true]);
        }
        $select->join(
                'articles_tags',
                'articles.id = articles_tags.article_id',
                [],
                $select::JOIN_LEFT
        );
        $select->where(['articles_tags.tag_id = ?' => $id]);
        $select->order('articles.id DESC');

        $resultSetPrototype = $this->postTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPostsByPublishDate($since, $to) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();
        $select->where(['isPublished' => true]);
        $select->where->greaterThanOrEqualTo('articles.published', $since);
        $select->where->lessThanOrEqualTo('articles.published', $to);
        $select->order('articles.id DESC');

        $resultSetPrototype = $this->postTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPostsByUserId($id, $hideUnpublished = true) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();
        if ($hideUnpublished) {
            $select->where(['isPublished' => true]);
        }
        $select->where(['author' => $id]);
        $select->order('articles.id DESC');

        $resultSetPrototype = $this->postTableGateway->getResultSetPrototype();
        $paginator = new Paginator(new DbSelect($select, $sql, $resultSetPrototype));

        return $paginator;
    }

    /**
     * {@see PostRepositoryInterface}
     */
    public function findPublishDates(string $group = 'year', string $limit = null) {
        $sql = $this->postTableGateway->getSql();
        $select = $sql->select();

        switch ($group) {
            case "day":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'month' => new Expression('MONTH(published)'),
                    'day' => new Expression('DAY(published)'),
                    'total' => new Expression('COUNT(*)')
                ));
                $select->group('day');
                $select->group('month');
                $select->group('year');
                $select->order('year DESC');
                $select->order('month DESC');
                $select->order('day DESC');
                break;

            case "month":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'month' => new Expression('MONTH(published)'),
                    'total' => new Expression('COUNT(*)'),
                ));
                $select->group('month');
                $select->group('year');
                $select->order('year DESC');
                $select->order('month DESC');
                break;

            case "year":
                $select->columns(array(
                    'year' => new Expression('YEAR(published)'),
                    'total' => new Expression('COUNT(*)')
                ));
                $select->group('year');
                $select->order('year DESC');
                break;

            default:
                throw new InvalidArgumentBlogException("Invalid parameter for group: must be day, month or year");
        }

        $select->where(['isPublished' => true]);

        if (StaticValidator::execute($limit, 'Digits')) {
            $select->limit($limit);
        }

        $paginator = new Paginator(new DbSelect($select, $sql));

        return $paginator;
    }

}

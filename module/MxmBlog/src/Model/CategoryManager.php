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

class CategoryManager implements CategoryManagerInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @param TableGateway $postTableGateway
     */
    public function __construct(
            TableGateway $tableGateway
    ) {
        $this->tableGateway = $tableGateway;
    }

    /**
     * {@see CategoryManagerInterface}
     */
    public function insertCategory(CategoryInterface $category) {
        $categoryHydrator = $this->tableGateway->getResultSetPrototype()->getHydrator();
        $categoryArray = $categoryHydrator->extract($category);
        unset($categoryArray['id']);
        $this->tableGateway->insert($categoryArray);
        $newId = $this->tableGateway->getLastInsertValue();
        if (!$newId) {
            throw new DataBaseErrorBlogException("Insert operation failed");
        }

        $resultSet = $this->tableGateway->select(['id' => $newId]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@see CategoryManagerInterface}
     */
    public function updateCategory(CategoryInterface $category) {
        $categoryHydrator = $this->tableGateway->getResultSetPrototype()->getHydrator();
        $categoryArray = $categoryHydrator->extract($category);
        unset($categoryArray['id']);
        $this->tableGateway->update($categoryArray, ['id = ?' => $category->getId()]);

        $resultSet = $this->tableGateway->select(['id' => $category->getId()]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@see CategoryManagerInterface}
     */
    public function deleteCategory(CategoryInterface $category) {
        return $this->tableGateway->delete(['id' => $category->getId()]);
    }

    /**
     * {@see CategoryManagerInterface}
     */
    public function deleteCategories($categories) {
        if ($categories instanceof Paginator) {
            $categories = iterator_to_array($categories->setItemCountPerPage(-1));
        }

        if (!is_array($categories)) {
            throw new InvalidArgumentBlogException(sprintf(
                            'The data must be array; received "%s"',
                            (is_object($categories) ? get_class($categories) : gettype($categories))
            ));
        }

        if (empty($categories)) {

            return 0;
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof CategoryInterface) {
                return $value->getId();
            } else {
                throw new InvalidArgumentBlogException(sprintf(
                                'Invalid value in data array detected, value must be a string or instance of CategoryInterface, %s given.',
                                (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $categoryIds = array_map($func, $categories);

        $sql = $this->tableGateway->getSql();
        $delete = $sql->delete();
        $delete->where->in('id', $categoryIds);
        $result = $this->tableGateway->deleteWith($delete);

        return $result;
    }

}

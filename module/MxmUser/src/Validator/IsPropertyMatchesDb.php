<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmUser\Validator;

use Zend\Db\Sql\Select;
use MxmBlog\Validator\IsPublishedRecordExistsValidatorInterface;
use MxmBlog\Model\PostInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Validator\Db\AbstractDb;

class IsPropertyMatchesDb extends AbstractDb //implements IsPublishedRecordExistsValidatorInterface
{
    protected $property;

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (array_key_exists('property', $options)) {
            $this->property = $options['property'];
        }
    }

    public function isValid($value)
    {
        /*
         * Check for an adapter being defined. If not, throw an exception.
         */
        if (null === $this->adapter) {
            throw new Exception\RuntimeException('No database adapter present');
        }

        /*
         * Check for an property being defined. If not, throw an exception.
         */
        if (empty($this->property)) {
            throw new Exception\RuntimeException('No checking property present');
        }

        /*
         * Check for an value is object. If not, throw an exception.
         */
        if (! is_object($value)) {
            throw new Exception\RuntimeException(sprintf(
                '%s expects an object; received "%s"',
                __METHOD__,
                gettype($value))
            );
        }

        $valid = false;

        $validatingPropertyMethod = 'get' . ucfirst($this->property);
        if(! is_callable([$value, $validatingPropertyMethod])) {
            throw new Exception\RuntimeException(sprintf(
                'Method for validating property "%s" is not callable',
                $this->property
            ));
        }

        $this->setValue($value->$validatingPropertyMethod());

        $conditionPropertyMethod = 'get' . ucfirst($this->field);
        if(! is_callable([$value, $conditionPropertyMethod])) {
            throw new Exception\RuntimeException(sprintf(
                'Method for condition property "%s" is not callable',
                $this->property
            ));
        }

        try {
            $result = $this->query($value->$conditionPropertyMethod());
        } catch (\Exception $ex) {
            $this->error($ex->getMessage());

            return $valid;
        }

        if (! $result) {
            $this->error(self::ERROR_NO_RECORD_FOUND);
        } else {
            if (array_key_exists($this->property, $result)) {
                $valid = $result[$this->property] === $value->$validatingPropertyMethod();
            }
        }

        return $valid;
    }

    protected function query($value)
    {
        $sql = new Sql($this->getAdapter());
        $select = $this->getSelect();
        $select->columns([$this->field, $this->property]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $parameters = $statement->getParameterContainer();
        $parameters['where1'] = $value;
        $result = $statement->execute();

        return $result->current();
    }
}
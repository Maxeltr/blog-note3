<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmUser\Mapper;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Config\Config;
use Zend\Hydrator\HydratorInterface;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\PreparableSqlInterface;
use MxmUser\Exception\RecordNotFoundUserException;
use MxmUser\Exception\InvalidArgumentUserException;
use MxmUser\Exception\DataBaseErrorUserException;
use MxmUser\Model\UserInterface;

class ZendDbSqlMapper implements MapperInterface
{
    /**
     * @var Zend\Db\Sql\Sql
     */
    protected $sql;

    /**
     * @var \Zend\Hydrator\HydratorInterface
     */
    protected $aggregateHydrator;

    /**
     * @var \Blog\Model\PostInterface
     */
    protected $userPrototype;

    /**
     * @var Zend\Config\Config;
     */
    protected $config;

    /**
     * @param Sql $sql
     * @param UserInterface $userPrototype
     * @param Config $config
     */
    public function __construct(
        Sql $sql,
        HydratorInterface $aggregateHydrator,
        UserInterface $userPrototype,
        Config $config
    ) {
        $this->sql = $sql;
        $this->aggregateHydrator = $aggregateHydrator;
        $this->userPrototype = $userPrototype;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function findUserById($id)
    {

        $parameters['where']['id'] = $id;

        $select = $this->createUserSelectQuery($parameters);

        return $this->createObject($select, $this->aggregateHydrator, $this->userPrototype);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByEmail($email)
    {

        $parameters['where']['email'] = $email;

        $select = $this->createUserSelectQuery($parameters);

        return $this->createObject($select, $this->aggregateHydrator, $this->userPrototype);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByResetPasswordToken($token)
    {

        $parameters['where']['passwordToken'] = $token;

        $select = $this->createUserSelectQuery($parameters);

        return $this->createObject($select, $this->aggregateHydrator, $this->userPrototype);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByEmailToken($token)
    {

        $parameters['where']['emailToken'] = $token;

        $select = $this->createUserSelectQuery($parameters);

        return $this->createObject($select, $this->aggregateHydrator, $this->userPrototype);
    }

    /**
     *
     * @param Select $select
     * @param HydratorInterface $hydrator
     * @param type $objectPrototype
     *
     * @return Object
     *
     * @throws InvalidArgumentUserException
     * @throws RecordNotFoundUserException
     */
    private function createObject(Select $select, HydratorInterface $hydrator, $objectPrototype)
    {
        if(!is_object($objectPrototype)) {
            throw new InvalidArgumentBlogException("ZendDbSqlMapper. createObject. No object param given.");
        }

        $stmt = $this->sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if($result instanceof ResultInterface && $result->isQueryResult() && $result->getAffectedRows()) {
            return $hydrator->hydrate($result->current(), $objectPrototype);
        }

        throw new RecordNotFoundUserException("ZendDbSqlMapper. createObject. Record with given ID not found.");
    }

    /**
     * {@inheritDoc}
     */
    public function findAllUsers()
    {
        $parameters = array(
            'group' => array(
                'id'
            ),
            'order' => array(
                'id' => 'DESC'
            )
        );

        $select = $this->createUserSelectQuery($parameters);

        return $this->createPaginator($select, $this->aggregateHydrator, $this->userPrototype);
    }

   /**
    * @param Select $select
    * @param HydratorInterface $hydrator
    * @param object $objectPrototype
    *
    * @return Paginator
    *
    * @throws InvalidArgumentUserException
    */
    private function createPaginator(Select $select, HydratorInterface $hydrator, $objectPrototype)
    {
        if(!is_object($objectPrototype)) {
            throw new InvalidArgumentUserException("ZendDbSqlMapper. createPaginator. No object param given.");
        }

        $resultSetPrototype = new HydratingResultSet($hydrator, $objectPrototype);

        // Create a new pagination adapter object:
        $paginatorAdapter = new DbSelect(
            // our configured select object:
            $select,
            // the adapter to run it against:
            $this->sql,
            // the result set to hydrate:
            $resultSetPrototype
        );
        $paginator = new Paginator($paginatorAdapter);

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function insertUser(UserInterface $userObject)
    {
        $data = $this->aggregateHydrator->extract($userObject);

        $action = new Insert('users');
        $action->values($data);

        $this->saveInDb($userObject, $action);

        return $userObject;
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser(UserInterface $userObject)
    {
        $data = $this->aggregateHydrator->extract($userObject);
        unset($data['id']);

        $action = new Update('users');
        $action->set($data);
        $action->where(array('id = ?' => $userObject->getId()));

        $this->saveInDb($userObject, $action);

        return $userObject;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser(UserInterface $userObject)
    {
        $action = new Delete('users');
        $action->where(array('id = ?' => $userObject->getId()));

        $stmt = $this->sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();

        return (bool)$result->getAffectedRows();
    }

    /**
     * Выполнить insert или update. Получить id и установить его в переданный объект.
     * @param object $object
     * @param PreparableSqlInterface $action
     *
     * @return object
     * @throws InvalidArgumentUserException
     * @throws DataBaseErrorUserException
     */
    private function saveInDb($object, PreparableSqlInterface $action)
    {
        if(!is_object($object)) {
            throw new InvalidArgumentUserException("ZendDbSqlMapper. saveInDb. No object param given.");
        }

        $stmt = $this->sql->prepareStatementForSqlObject($action);
        $result = $stmt->execute();

        if ($result instanceof ResultInterface) {
            $newId = $result->getGeneratedValue();
            if ($newId) {
                if(is_callable([$object, 'setId'])) {
                    $object->setId($newId);
                }
            }

            return $object;
        }

        throw new DataBaseErrorUserException("Database error. ZendDbSqlMapper. saveInDb. No ResultInterface returned.");
    }

    /**
     * Сформировать запрос select.
     * @param array $parameters
     *
     * @return Zend\Db\Sql\Select
     */
    private function createUserSelectQuery($parameters)
    {
        $select = new Select('users');

        if(array_key_exists('where', $parameters) && is_array($parameters['where'])) {
            foreach($parameters['where'] as $column => $value) {
                $select->where(array('users.' . $column . ' = ?' => $value));
            }
        }

        if(array_key_exists('like', $parameters) && is_array($parameters['like'])) {
            foreach($parameters['like'] as $column => $value) {
                $select->where->like('users.' . $column, '%' . $value . '%');
            }
        }

        if(array_key_exists('lessThanOrEqualTo', $parameters) && is_array($parameters['lessThanOrEqualTo'])) {
            foreach($parameters['lessThanOrEqualTo'] as $column => $value) {
                $select->where->lessThanOrEqualTo('users.' . $column, $value);
            }
        }

        if(array_key_exists('greaterThanOrEqualTo', $parameters) && is_array($parameters['greaterThanOrEqualTo'])) {
            foreach($parameters['greaterThanOrEqualTo'] as $column => $value) {
                $select->where->greaterThanOrEqualTo('users.' . $column, $value);
            }
        }

        $select->columns(array(
            'id' => 'id',
            'username' => 'username',
            'email' => 'email',
            'emailVerification' => 'emailVerification',
            'emailToken' => 'emailToken',
            'dateEmailToken' => 'dateEmailToken',
            'password' => 'password',
            'role' => 'role',
            'timebelt' => 'timebelt',
            'created' => 'created',
            'passwordToken' => 'passwordToken',
            'dateToken' => 'dateToken',
            'locale' => 'locale',
        ));

        if (array_key_exists('group', $parameters) && is_array($parameters['group'])) {
            foreach($parameters['group'] as $column) {
                $select->group('users.' . $column);
            }
        } else {
            $select->group('users.id');
        }

        if (array_key_exists('order', $parameters) && is_array($parameters['order'])) {
            foreach($parameters['order'] as $column => $value) {
                $select->order('users.' . $column . ' ' . $value);
            }
        }

        return $select;
    }
}
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

namespace MxmApi\Service;

use Zend\Crypt\Password\Bcrypt;
use MxmUser\Model\UserInterface;
use MxmUser\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use MxmRbac\Service\AuthorizationService;
use MxmApi\Exception\RuntimeException;
use MxmApi\Exception\ExpiredException;
use MxmApi\Exception\NotAuthenticatedException;
use MxmApi\Exception\InvalidArgumentException;
use MxmApi\Exception\RecordNotFoundException;
use MxmApi\Exception\AlreadyExistsException;
use MxmApi\Exception\InvalidPasswordException;
use MxmApi\Exception\NotAuthorizedException;
use MxmApi\Exception\DataBaseErrorException;

class ApiService implements ApiServiceInterface
{
    /**
     * @var DateTimeInterface;
     */
    protected $datetime;

    /**
     * @var Zend\Authentication\AuthenticationService;
     */
    protected $authenticationService;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Crypt\Password\Bcrypt
     */
    protected $bcrypt;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Bcrypt $bcrypt
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->bcrypt = $bcrypt;
    }

    /**
     * {@inheritDoc}
     */
    public function addClient($data)
    {
        if (!$this->authenticationService->hasIdentity()) {
            throw new NotAuthenticatedException('The user is not logged in');
        }

        if (!$this->authorizationService->isGranted('add.client')) {
            throw new NotAuthorizedException('Access denied. Permission "add.client" is required.');
        }

        $this->tableGateway->insert([
            'client_id' => $data['id'],
            'client_secret' => $data['client_secret'],
            'grant_types' => $data['grant_types'],
            'scope' => $data['scope'],
            'user_id' => $this->authenticationService->getIdentity()->getId()
        ]);

        $resultSet = $this->tableGateway->select(['id' => $id]);
        //if (0 === count($resultSet)) {
        //    throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        //}

		//return $resultSet->current();

        if ($resultSet instanceof ResultInterface) {
            $newId = $resultSet->getGeneratedValue();
            \zend\debug\debug::dump($newId);
			die();

            return $object;
        }


    }




}
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

use MxmApi\Model\ApiInterface;

interface ApiServiceInterface
{
    /**
     * Добавить данные клиента (приложение, сайт) в базу данных.
     *
     * @param  array $data
     * @return array|\ArrayObject|null
     * @throws InvalidArgumentException
     * @throws NotAuthenticatedException
     * @throws NotAuthorizedException
     * @throws AlreadyExistsException
     */
    public function addClient($data);

    /**
     * Извлечь данные клиента (приложение, сайт) из базы данных.
     *
     * @param  string $client_id
     * @return array|\ArrayObject|null
     * @throws InvalidArgumentException
     * @throws NotAuthenticatedException
     * @throws NotAuthorizedException
     * @throws RecordNotFoundException
     */
    public function findClientById($client_id);

    /**
     * Отозвать токен клиента (приложение, сайт).
     *
     * @param  array $client
     * @return int
     * @throws InvalidArgumentException
     * @throws NotAuthenticatedException
     * @throws NotAuthorizedException
     */
    public function revokeToken($client);

    /**
     * Извлечь данные клиентов (приложение, сайт) из базы данных.
     *
     * @return Zend\Paginator\Paginator
     * @throws NotAuthenticatedException
     * @throws NotAuthorizedException
     * @throws DataBaseErrorException
     */
    public function findAllClients();

    /**
     * Удалить данные клиента (приложение, сайт) из базы данных.
     *
     * @param  array $client
     * @return int
     * @throws InvalidArgumentException
     * @throws NotAuthenticatedException
     * @throws NotAuthorizedException
     */
    public function deleteClient($client);
}
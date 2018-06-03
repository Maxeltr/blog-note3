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

namespace MxmApi\Mapper;

use MxmUser\Model\UserInterface;
use MxmApi\Model\ClientInterface;

interface MapperInterface
{
    /**
     * @param ClientInterface $client
     *
     * @return ClientInterface
     * @throw DataBaseErrorException
     */
    public function insertClient(ClientInterface $client);

    /**
     * @param int|string $clientId
     *
     * @return ClientInterface
     * @throw RecordNotFoundException
     */
    public function findClientById($clientId);

    /**
     * @return Zend\Paginator\Paginator
     */
    public function findAllClients();

    /**
     * @param UserInterface $user
     *
     * @return Zend\Paginator\Paginator
     */
    public function findClientsByUser(UserInterface $user);

    /**
     * @param ClientInterface $client
     *
     * @return int
     */
    public function deleteToken(ClientInterface $client);

    /**
     * @param array|Paginator $clients
     *
     * @return int
     */
    public function deleteTokens($clients);

    /**
     * @param ClientInterface $client
     *
     * @return int
     */
    public function deleteClient(ClientInterface $client);

    /**
     * @param array|Paginator $clients
     *
     * @return int
     */
    public function deleteClients($clients);

    /**
     * @return Zend\Paginator\Paginator
     */
    public function findAllFiles();

    /**
     * @param UserInterface $user
     *
     * @return Zend\Paginator\Paginator
     */
    public function findAllFilesByUser(UserInterface $user = null);
}
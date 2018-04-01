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

namespace MxmApi\Model;

interface ClientInterface
{
    /**
     * @return string Client id
     */
    public function getClientId();

    /**
     * Set client id
     *
     * @param string $clientId Client id
     * @return object $this
     */
    public function setClientId($clientId);

    /**
     * @return string Client password
     */
    public function getClientSecret();

    /**
     * Set client password
     *
     * @param string $clientSecret Client password
     * @return object $this
     */
    public function setClientSecret($clientSecret);

    /**
     * @return string Client grant type
     */
    public function getGrantTypes();

    /**
     * Set client grant types
     *
     * @param string $grantTypes Client grant types
     * @return object $this
     */
    public function setGrantTypes($grantTypes);

    /**
     * @return string Client scope
     */
    public function getScope();

    /**
     * Set client scope
     *
     * @param string $scope Client scope
     * @return object $this
     */
    public function setScope($scope);

    /**
     * @return string User id
     */
    public function getUserId();

    /**
     * Set user id for client
     *
     * @param string $userId User id
     * @return object $this
     */
    public function setUserId($userId);
}

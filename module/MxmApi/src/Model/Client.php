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

class Client
{
    /**
     * @var string ID
     */
    protected $clientId;

    /**
     * @var string client secret (password)
     */
    protected $clientSecret;

    /**
     * @var string grant types
     */
    protected $grantTypes;

    /**
     * @var string scope
     */
    protected $scope;

    /**
     * @var string user id
     */
    protected $userId;

    /**
     *  {@inheritDoc}
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     *  {@inheritDoc}
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     *  {@inheritDoc}
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getGrantTypes()
    {
        return $this->grantTypes;
    }

    /**
     *  {@inheritDoc}
     */
    public function setGrantTypes($grantTypes)
    {
        $this->grantTypes = $grantTypes;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     *  {@inheritDoc}
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     *  {@inheritDoc}
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }
}
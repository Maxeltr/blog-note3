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

namespace MxmFile\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;
use MxmApi\Mapper\MapperInterface;
use MxmApi\Model\ClientInterface;

class ClientStrategy implements StrategyInterface
{
    /**
     * @var MxmApi\Mapper\MapperInterface
     */
    private $clientMapper;

    /**
     * @param MxmApi\Mapper\MapperInterface $clientMapper
     */
    public function __construct(MapperInterface $clientMapper)
    {
        $this->clientMapper = $clientMapper;
    }

    /**
     * Converts ClientInterface to id string
     *
     * @param ClientInterface $value
     *
     * @return mixed|string
     */
    public function extract($value, ?object $object = null)
    {
        if ($value instanceof ClientInterface) {
            return $value->getClientId();
        }

        return $value;
    }

    /**
     * Converts id string to ClientInterface instance for injecting to object
     *
     * @param mixed|string $value
     *
     * @return mixed|ClientInterface
     */
    public function hydrate($value, ?array $data)
    {
        if (empty($value)) {
            return $value;
        }

        try {
            $client = $this->clientMapper->findClientById($value);
        } catch (\Exception $ex) {
            return $value;
        }

        return $client;
    }
}

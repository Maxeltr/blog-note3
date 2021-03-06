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

namespace MxmUser\Hydrator\UserFormHydrator;

use MxmUser\Model\UserInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Config\Config;

class RoleHydrator implements HydratorInterface
{
    protected $config;
    protected $roleNames;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $roles = $this->config->rbac_module->rbac_config->roles;
        $this->roleNames = [];
        foreach  ($roles as $role => $options) {
            $this->roleNames[] = $role;
        }
    }

    public function hydrate(array $data, $object)
    {
        if (!$object instanceof UserInterface) {
            return $object;
        }

        if (array_key_exists('role', $data)) {
            $object->setRole($this->roleNames[$data['role']]);
        }

        return $object;
    }

    public function extract($object) : array
    {
        if (!$object instanceof UserInterface) {
            return [];
        }

        $values ['role'] = array_search($object->getRole(), $this->roleNames);

        return $values;
    }
}
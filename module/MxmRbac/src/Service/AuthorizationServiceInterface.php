<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

namespace MxmRbac\Service;

use MxmUser\Model\UserInterface;

interface AuthorizationServiceInterface
{

    /**
     * Check if the permission is granted to the current identity
     *
     * @param string $permission
     * @param mixed $content
     *
     * @return bool
     */
    public function isGranted($permission, $content = null);

    /**
     * Returns true if the permission is granted to the current identity. Throw exception if it is not granted.
     *
     * @return bool
     * @throws NotAuthorizedException
     */
    public function checkPermission($permission, $content = null);

    /**
     * Set current identity
     *
     * @param MxmUser\Model\UserInterface $identity
     *
     * @return $this
     */
    public function setIdentity(UserInterface $identity);

    /**
     * Get current identity
     *
     * @return MxmUser\Model\UserInterface
     */
    public function getIdentity();

    /**
     * Check if the given role match one of the identity roles
     *
     * @param string|RoleInterface $objectOrName
     *
     * @return bool
     */
    public function matchIdentityRoles($objectOrName);

    /**
     * Check if the given UserInterface object match the identity
     *
     * @param UserInterface $object
     *
     * @return bool
     */
    public function matchUserIds($object);
}

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

namespace MxmUser\Model;

class DeletedUser extends User implements UserInterface
{
    /**
     * @var string Deletion reason
     */
    protected $deletionReason;

    /**
     * @var string Deletion Date
     */
    protected $deletionDate;

    /**
     * @var string Id of user, which deleted this DeletedUser
     */
    protected $executor;

    /**
     *  {@inheritDoc}
     */
    public function getDeletionReason()
    {
        return $this->deletionReason;
    }

    /**
     *  {@inheritDoc}
     */
    public function setDeletionReason($deletionReason)
    {
        $this->deletionReason = $deletionReason;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getDeletionDate()
    {
        return $this->deletionDate;
    }

    /**
     *  {@inheritDoc}
     */
    public function setDeletionDate($deletionDate)
    {
        $this->deletionDate = $deletionDate;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     *  {@inheritDoc}
     */
    public function setExecutor($executor)
    {
        $this->executor = $executor;

        return $this;
    }
}
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

namespace MxmFile\Model;

use DateTimeInterface;
use MxmUser\Model\UserInterface;
use MxmApi\Model\ClientInterface;

class File implements FileInterface
{
    /**
     * @var String
     */
    protected $fileId;

    /**
     * @var String
     */
    protected $filename;

    /**
     * @var String
     */
    protected $path;

    /**
     * @var String
     */
    protected $description;

    /**
     * @var DateTimeInterface
     */
    protected $uploadDate;

    /**
     * @var UserInterface
     */
    protected $owner;

    /**
     * @var String
     */
    protected $size;

    /**
     * @var DateTimeInterface
     */
    protected $changeDate;

    /**
     * @var String
     */
    protected $type;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     *  {@inheritDoc}
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     *  {@inheritDoc}
     */
    public function setFileId($fileId)
    {
        $this->fileId = (string) $fileId;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     *  {@inheritDoc}
     */
    public function setFilename($filename)
    {
        $this->filename = (string) $filename;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *  {@inheritDoc}
     */
    public function setPath($path)
    {
        $this->path = (string) $path;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *  {@inheritDoc}
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     *  {@inheritDoc}
     */
    public function setUploadDate(DateTimeInterface $uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     *  {@inheritDoc}
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     *  {@inheritDoc}
     */
    public function setSize($size)
    {
        $this->size = (string) $size;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getChangeDate()
    {
        return $this->changeDate;
    }

    /**
     *  {@inheritDoc}
     */
    public function setChangeDate(DateTimeInterface $changeDate)
    {
        $this->changeDate = $changeDate;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *  {@inheritDoc}
     */
    public function setType($type)
    {
        $this->type = (string) $type;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *  {@inheritDoc}
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }
}
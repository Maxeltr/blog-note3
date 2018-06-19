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

interface FileInterface
{
    /**
     * Get file ID
     *
     * @return String
     */
    public function getFileId();

    /**
     * Set file ID
     *
     * @param String $fileId
     *
     * @return $this
     */
    public function setFileId($fileId);

    /**
     * Get filename
     *
     * @return String
     */
    public function getFilename();

    /**
     * Set filename
     *
     * @param String $filename
     *
     * @return $this
     */
    public function setFilename($filename);

    /**
     * Get path of file
     *
     * @return String
     */
    public function getPath();

    /**
     * Set path of file
     *
     * @param String $path
     *
     * @return $this
     */
    public function setPath($path);

    /**
     * Get description of file
     *
     * @return String
     */
    public function getDescription();

    /**
     * Set description of file
     *
     * @param String $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get upload date of file
     *
     * @return UserInterface
     */
    public function getUploadDate();

    /**
     * Set upload date of file
     *
     * @param DateTimeInterface $uploadDate
     *
     * @return $this
     */
    public function setUploadDate(DateTimeInterface $uploadDate);


    /**
     * Get owner of file
     *
     * @return UserInterface
     */
    public function getOwner();

    /**
     * Set owner of file
     *
     * @param UserInterface $owner
     *
     * @return $this
     */
    public function setOwner(UserInterface $owner);

    /**
     * Get size of file
     *
     * @return String
     */
    public function getSize();

    /**
     * Set size of file
     *
     * @param String $size
     *
     * @return $this
     */
    public function setSize($size);

    /**
     * Get date of change
     *
     * @return DateTimeInterface
     */
    public function getChangeDate();

    /**
     * Set date of change
     *
     * @param DateTimeInterface $changeDate
     *
     * @return $this
     */
    public function setChangeDate(DateTimeInterface $changeDate);

    /**
     * Get file type
     *
     * @return String
     */
    public function getType();

    /**
     * Set file type
     *
     * @param String $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * Get Client
     *
     * @return ClientInterface
     */
    public function getClient();

    /**
     * Set Client
     *
     * @param ClientInterface $client
     *
     * @return $this
     */
    public function setClient(ClientInterface $client);
}
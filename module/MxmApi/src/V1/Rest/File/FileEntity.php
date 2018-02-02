<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmApi\V1\Rest\File;

class FileEntity
{
    /**
     * @var String
     */
    protected $id;

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
     * @var String
     */
    protected $uploaded;

    /**
     * @var String
     */
    protected $owner;

    /**
     * Get file ID
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set file ID
     *
     * @param String
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get file name
     *
     * @return String
     */
    public function getFilename()
    {
        return $this->filename;
    }
    /**
     * Set file name
     *
     * @param String
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

     /**
     * Get file path
     *
     * @return String
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * Set file path
     *
     * @param String
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get file description
     *
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Set file description
     *
     * @param String
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get file upload time
     *
     * @return String
     */
    public function getUploaded()
    {
        return $this->uploaded;
    }
    /**
     * Set file upload time
     *
     * @param String
     */
    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    /**
     * Get file owner id
     *
     * @return String
     */
    public function getOwner()		
    {
        return $this->owner;
    }
    /**
     * Set file owner id
     *
     * @param String
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}
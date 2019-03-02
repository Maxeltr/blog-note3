<?php

/*
 * The MIT License
 *
 * Copyright 2019 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmGame\Model;

use \DateTimeInterface;

class Texture //implements TextureInterface
{
    /**
     * @var string ID
     */
    protected $textureId;

    /**
     * @var string Name
     */
    protected $textureName;

    /**
     * @var string Description
     */
    protected $description;

    /**
     * @var DateTimeInterface Upload date
     */
    protected $uploadDate;

    /**
     * @var string Path to texture file
     */
    protected $path;

    /**
     *  {@inheritDoc}
     */
    public function getTextureId()
    {
        return $this->textureId;
    }

    /**
     *  {@inheritDoc}
     */
    public function setTextureId($textureId)
    {
        $this->textureId = (string) $textureId;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getTextureName()
    {
        return $this->textureName;
    }

    /**
     *  {@inheritDoc}
     */
    public function setTextureName($textureName)
    {
        $this->textureName = (string) $textureName;

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
    public function getPath()
    {
        return $this->path;
    }

    /**
     *  {@inheritDoc}
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
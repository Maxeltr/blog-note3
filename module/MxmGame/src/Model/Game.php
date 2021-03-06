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

class Game implements GameInterface
{
    /**
     * @var string ID
     */
    protected $gameId;

    /**
     * @var string Title
     */
    protected $title;

    /**
     * @var string Description
     */
    protected $description;

    /**
     * @var DateTimeInterface Upload date
     */
    protected $uploadDate;

    /**
     * @var DateTimeInterface Publish date
     */
    protected $publishDate;

    /**
     * @var DateTimeInterface Update date
     */
    protected $updateDate;

    /**
     * @var bool Is game published?
     */
    protected $isPublished;

    /**
     *  {@inheritDoc}
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     *  {@inheritDoc}
     */
    public function setGameId($gameId)
    {
        $this->gameId = (string) $gameId;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *  {@inheritDoc}
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;

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
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     *  {@inheritDoc}
     */
    public function setPublishDate(DateTimeInterface $publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     *  {@inheritDoc}
     */
    public function setUpdateDate(DateTimeInterface $updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     *  {@inheritDoc}
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = (bool) $isPublished;

        return $this;
    }
}
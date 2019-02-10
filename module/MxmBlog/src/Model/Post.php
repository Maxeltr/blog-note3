<?php

/*
 * The MIT License
 *
 * Copyright 2016 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmBlog\Model;

use MxmBlog\Model\CategoryInterface;
use MxmBlog\Model\AbstractModel;
use Zend\Tag\ItemList;
use MxmUser\Model\UserInterface;
use DateTimeInterface;

class Post extends AbstractModel implements PostInterface
{
    /**
     * @var int ID
     */
    protected $id;

    /**
     * @var string Title
     */
    protected $title;

    /**
     * @var string Text
     */
    protected $text;

    /**
     * @var string Summary
     */
    protected $summary;

    /**
     * @var CategoryInterface
     */
    protected $category;

    /**
     * @var UserInterface author
     */
    protected $author;

    /**
     * @var DateTimeInterface Created
     */
    protected $created;

    /**
     * @var DateTimeInterface Updated
     */
    protected $updated;

    /**
     * @var DateTimeInterface Published
     */
    protected $published;

    /**
     * @var bool Is published?
     */
    protected $isPublished;

    /**
     * @var int Version
     */
    protected $version;

    /**
     * @var Zend\Tag\ItemList;
     */
    protected $tags;

    /**
     * Принудительно копируем this->object, иначе
     * он будет указывать на один и тот же объект.
     */
    public function __clone()
    {
        if ($this->category instanceof CategoryInterface) {
            $this->category = clone $this->category;
        }
        if ($this->tags instanceof ItemList) {
            $this->tags = clone $this->tags;
        }
        if ($this->created instanceof DateTimeInterface) {
            $this->created = clone $this->created;
        }
        if ($this->updated instanceof DateTimeInterface) {
            $this->updated = clone $this->updated;
        }
        if ($this->published instanceof DateTimeInterface) {
            $this->published = clone $this->published;
        }
        if ($this->author instanceof UserInterface) {
            $this->author = clone $this->author;
        }
    }

    /**
     *  {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = (int) $id;

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
     * {@inheritDoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     *  {@inheritDoc}
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * {@inheritDoc}
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * {@inheritDoc}
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * {@inheritDoc}
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreated(\DateTimeInterface $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdated(\DateTimeInterface $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * {@inheritDoc}
     */
    public function setPublished(\DateTimeInterface $published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = (bool) $isPublished;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritDoc}
     */
    public function setVersion($version)
    {
        $this->version = (int) $version;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * {@inheritDoc}
     */
    public function setTags(ItemList $tags)
    {
        $this->tags = $tags;

        return $this;
    }
}
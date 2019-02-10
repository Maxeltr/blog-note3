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

use Zend\Tag\ItemList;
use MxmUser\Model\UserInterface;
use DateTimeInterface;

interface PostInterface
{
    /**
     * Возвращает клон PostInterface для правильной гидрации.
     *
     * @return PostInterface
     */
    public function __clone();

    /**
     * Возвращает ID записи
     *
     * @return int ID
     */
    public function getId();

    /**
     * Возвращает заголовок записи
     *
     * @return string Заголовок
     */
    public function getTitle();

    /**
     * Возвращает текст записи
     *
     * @return string Текст записи
     */
    public function getText();

    /**
     * Возвращает текст краткого описания
     *
     * @return string
     */
    public function getSummary();

    /**
     * Возвращает объект категории
     *
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * Возвращает автора записи
     *
     * @return UserInterface
     */
    public function getAuthor();

    /**
     * Возвращает дату и время создания записи
     *
     * @return DateTimeInterface
     */
    public function getCreated();

    /**
     * Возвращает дату и время изменения записи
     *
     * @return DateTimeInterface
     */
    public function getUpdated();

    /**
     * Возвращает дату и время публикации записи
     *
     * @return DateTimeInterface
     */
    public function getPublished();

    /**
     * Возвращает флаг публикации (true - опубликовано, false - не опубликовано)
     *
     * @return bool
     */
    public function getIsPublished();

    /**
     * Возвращает кол-во изменений
     *
     * @return int
     */
    public function getVersion();

    /**
     * Возвращает теги
     *
     * @return Zend\Tag\ItemList
     */
    public function getTags();

    /**
     * Устанавливает ID записи
     * @param string $id ID записи.
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Устанавливает заголовок записи
     * @param string $title Заголовок записи.
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * Устанавливает текст записи
     * @param string $text Текст записи.
     *
     * @return $this
     */
    public function setText($text);

    /**
     * Устанавливает текст краткого описания
     * @param string $summary Текст краткого описания.
     *
     * @return $this
     */
    public function setSummary($summary);

    /**
     * Устанавливает объект категории
     * @param CategoryInterface $category Объект категория.
     *
     * @return $this
     */
    public function setCategory(CategoryInterface $category);

    /**
     * Устанавливает автора записи
     * @param UserInterface $author Автор записи.
     *
     * @return $this
     */
    public function setAuthor(UserInterface $author);

    /**
     * Устанавливает дату и время создания записи.
     * @param DateTimeInterface $created Дата и время создания записи.
     *
     * @return $this
     */
    public function setCreated(\DateTimeInterface $created);

    /**
     * Устанавливает дату и время изменения записи
     * @param DateTimeInterface $updated Дата и время изменения записи.
     *
     * @return $this
     */
    public function setUpdated(\DateTimeInterface $updated);

    /**
     * Устанавливает дату и время публикации записи
     * @param DateTimeInterface $published Дата и время публикации записи.
     *
     * @return $this
     */
    public function setPublished(\DateTimeInterface $published);

    /**
     * Устанавливает флаг публикации (true - опубликовано, false - не опубликовано)
     * @param bool $isPublished Флаг публикации.
     *
     * @return $this
     */
    public function setIsPublished($isPublished);

    /**
     * Устанавливает кол-во изменений
     * @param int $version Кол-во изменений.
     *
     * @return $this
     */
    public function setVersion($version);

    /**
     * Устанавливает теги.
     * @param array|Zend\Tag\ItemList $tags объект ItemList
     *
     * @return $this
     * @throw \InvalidArgumentException
     */
    public function setTags(ItemList $tags);
}
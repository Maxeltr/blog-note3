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

namespace MxmBlog\Service;


use MxmBlog\Model\PostInterface;
use MxmBlog\Model\CategoryInterface;
use MxmBlog\Model\TagInterface;
use MxmUser\Model\UserInterface;

interface PostServiceInterface
{
    /**
     * Найти все статьи, если $hideUnpublished = false. Найти все
     * опубликованные статьи, если $hideUnpublished = true.
     *
     * @param bool $hideUnpublished
     *
     * @throw NotAuthenticatedBlogException
     * @throw NotAuthorizedBlogException
     *
     * @return Paginator
     */
    public function findAllPosts($hideUnpublished = true);

    /**
     * Должен вернуть Paginator
     *
     * @param UserInterface $user
     *
     * @return Paginator
     */
    public function findPostsByUser(UserInterface $user);

    /**
     * Должен вернуть Paginator
     *
     * @param UserInterface $user
     *
     * @return Paginator
     */
    public function findUnpublishedPostsByUser(UserInterface $user);

    /**
     * Должен Paginator
     *
     * @param CategoryInterface $category
     *
     * @return Paginator
     */
    public function findPostsByCategory(CategoryInterface $category);

    /**
     * Должен вернуть Paginator
     *
     * @param TagInterface $tag
     *
     * @return Paginator
     */
    public function findPostsByTag(TagInterface $tag);

    /**
     * Должен вернуть один объект по id, реализующий PostInterface
     *
     * @param int $id
     * @return PostInterface
     * @throw RecordNotFoundBlogException
     */
    public function findPostById($id);

    /**
     * Должен сохранять объект, реализующий PostInterface и возвращать его же.
     * Также должен сохранять связи тег-статья в соответствующей таблице БД и тег.
     *
     * @param  PostInterface $post
     * @return PostInterface
     * @throw NotAuthorizedBlogException
     */
    public function insertPost(PostInterface $post);

    /**
     * Должен обновить объект, реализующий PostInterface и возвращать его же.
     * Также должен сохранять связи тег-статья в соответствующей таблице БД.
     *
     * @param  PostInterface $post
     * @return PostInterface
     * @throw NotAuthorizedBlogException
     */
    public function updatePost(PostInterface $post);

    /**
     * Должен удалить полученный объект, реализующий PostInterface, и его связи
     * с тегами, и вернуть true (если удалено) или false (если неудача).
     *
     * @param  PostInterface $post
     *
     * @return bool
     * @throw NotAuthorizedBlogException
     */
    public function deletePost(PostInterface $post);

    /**
     * Должен вернуть массив объектов, реализующих CategoryInterface или Paginator
     *
     * @return Paginator
     */
    public function findAllCategories();

    /**
     * Должен вернуть один объект по id, реализующий CategoryInterface
     *
     * @param int $id
     * @return CategoryInterface
     * @throw RecordNotFoundBlogException
     */
    public function findCategoryById($id);

    /**
     * Должен сохранять объект, реализующий CategoryInterface и возвращать его же.
     *
     * @param  CategoryInterface $category
     * @return CategoryInterface
     * @throw NotAuthorizedBlogException
     */
    public function insertCategory(CategoryInterface $category);

    /**
     * Должен обновить объект, реализующий CategoryInterface и возвращать его же.
     *
     * @param  CategoryInterface $category
     * @return CategoryInterface
     * @throw NotAuthorizedBlogException
     */
    public function updateCategory(CategoryInterface $category);

    /**
     * Должен удалить полученный объект, реализующий CategoryInterface.
     *
     * @param  CategoryInterface $category
     *
     * @return bool
     * @throw NotAuthorizedBlogException
     */
    public function deleteCategory(CategoryInterface $category);

    /**
     * Удалить несколько категорий.
     *
     * @param array $categories
     *
     * @return bool
     * @throw NotAuthorizedBlogException
     * @throws InvalidArgumentBlogException
     */
    public function deleteCategories($categories);

    /**
     * Должен вернуть массив объектов, реализующих TagInterface или Paginator
     *
     * @return Paginator
     */
    public function findAllTags();

    /**
     * Должен вернуть один объект по id, реализующий TagInterface
     *
     * @param int $id
     *
     * @return TagInterface
     * @throw RecordNotFoundBlogException
     */
    public function findTagById($id);

    /**
     * Должен сохранять объект, реализующий TagInterface и возвращать его же.
     *
     * @param  TagInterface $tag
     * @return TagInterface
     * @throw NotAuthorizedBlogException
     */
    public function insertTag(TagInterface $tag);

    /**
     * Должен обновить объект, реализующий TagInterface и возвращать его же.
     *
     * @param  TagInterface $tag
     * @return TagInterface
     * @throw NotAuthorizedBlogException
     */
    public function updateTag(TagInterface $tag);

    /**
     * Должен удалить полученный объект, реализующий TagInterface и
     * его связи со статьями.
     *
     * @param  TagInterface $tag
     *
     * @return bool
     * @throw NotAuthorizedBlogException
     */
    public function deleteTag(TagInterface $tag);

    /**
     * Найти статьи за определенный период времени.
     *
     * @param  string $since Найти даты с этой даты.
     * @param  string $to Найти статьи до данной даты..
     *
     * @return Paginator
     */
    public function findPostsByPublishDate(\DateTimeInterface $since, \DateTimeInterface $to);

    /**
     * Найти даты, в которые были опубликованы статьи.
     *
     * @param  string $group Сгруппировать по дням, месяцам или годам.
     *
     * @return Paginator
     */
    public function findPublishDates($group);

    /**
     * Получить приветственную надпись для главной страницы в виде
     * массива ['caption' => 'Hello, world!', 'message' => 'bla-bla-bla'].
     *
     * @return array ['caption' => 'Hello, world!', 'message' => 'bla-bla-bla']
     *
     * @throws Zend\Config\Exception\InvalidArgumentException
     * @throws Zend\Config\Exception\RuntimeException
     */
    public function getGreeting();

    /**
     * Изменить приветственную надпись для главной страницы.
     *
     * @param array $greeting Массив должен иметь ключи caption и message.
     *
     * @return array ['caption' => 'Hello, world!', 'message' => 'bla-bla-bla']
     *
     * @throws Zend\Config\Exception\InvalidArgumentException
     * @throws Zend\Config\Exception\RuntimeException
     * @throws MxmBlog\Exception\RuntimeBlogException
     */
    public function editGreeting($greeting);
}
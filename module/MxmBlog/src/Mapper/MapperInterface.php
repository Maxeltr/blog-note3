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

namespace MxmBlog\Mapper;

use MxmBlog\Model\PostInterface;
use MxmBlog\Model\CategoryInterface;
use MxmBlog\Model\TagInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use MxmBlog\Service\DateTimeInterface;

interface MapperInterface
{
    /**
     * @param int|string $id
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return PostInterface
     * @throw RecordNotFoundBlogException
     * @throw InvalidArgumentBlogException
     */
    public function findPostById($id, $hideUnpublished);
    
    /**
     * @param string $name
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     * 
     * @return Paginator
     */
    public function findPostsByName($name, $hideUnpublished);
    
    /**
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     * 
     * @return Paginator
     */
    public function findAllPosts($hideUnpublished);
    
    /**
     * @param PostInterface $post
     *
     * @return PostInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function insertPost(PostInterface $post);
    
    /**
     * @param PostInterface $post
     *
     * @return PostInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function updatePost(PostInterface $post);
    
    /**
     * @param PostInterface $post
     * Должен удалить полученный объект, реализующий PostInterface, и его связи
     * с тегами, и вернуть true (если удалено) или false (если неудача).
     * 
     * @return bool
     */
    public function deletePost(PostInterface $post);
    
    /**
     * @param CategoryInterface $category
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
    public function findPostsByCategory(CategoryInterface $category, $hideUnpublished);
    
    /**
     * @param TagInterface $tag
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
    public function findPostsByTag(TagInterface $tag, $hideUnpublished);
    
    /**
     * Найти статьи, написанные определенным пользователем.
     *
     * @param UserInterface $user
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     * 
     * @return Paginator
     */
    public function findPostsByUser(/*UserInterface*/ $user, $hideUnpublished);
    
    /**
     * Найти статьи по дате создания.
     * 
     * @param DateTime $since
     * @param DateTime $to
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
    public function findPostsByCreateDate(DateTimeInterface $since, DateTimeInterface $to, $hideUnpublished);
    
    /**
     * Найти статьи по дате редактирования.
     *
     * @param DateTime $since
     * @param DateTime $to
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
    public function findPostsByUpdateDate(DateTimeInterface $since, DateTimeInterface $to, $hideUnpublished);
    
    /**
     * Найти статьи по дате публикации.
     *
     * @param DateTime $since
     * @param DateTime $to
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
    public function findPostsByPublishDate(DateTimeInterface $since, DateTimeInterface $to, $hideUnpublished);
    
    /**
     * Найти даты, когда были публикации и подсчитать кол-во публикаций.
     * Сгруппировать по годам (year), месяцам (month) или дням (day).
     *
     * @param string $group
     *
     * @return Zend\Db\ResultSet
     */
    public function findPublishDates($group, $limit);
    
    /**
     * @param int|string $id
     *
     * @return CategoryInterface
     * @throw RecordNotFoundBlogException
     * @throw InvalidArgumentBlogException
     */
    public function findCategoryById($id);
    
    /**
     * @param string $name
     * 
     * @return Paginator
     */
    public function findCategoriesByName($name);
    
    /**
     * 
     * @return Paginator
     */
    public function findAllCategories();
    
    /**
     * @param CategoryInterface $category
     *
     * @return CategoryInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function insertCategory(CategoryInterface $category);
    
    /**
     * @param CategoryInterface $category
     *
     * @return CategoryInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function updateCategory(CategoryInterface $category);
    
    /**
     * @param CategoryInterface $category
     *
     * @return bool
     */
    public function deleteCategory(CategoryInterface $category);
    
    /**
     * @param int|string $id
     *
     * @return TagInterface
     * @throw RecordNotFoundBlogException
     * @throw InvalidArgumentBlogException
     */
    public function findTagById($id);
     
    /**
     * @param string $name
     *
     * @return Paginator
     */
    public function findTagsByName($name);
    
    /**
     * 
     * @return Paginator
     */
    public function findAllTags();
        
    /**
     * @param TagInterface $tag
     *
     * @return TagInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function insertTag(TagInterface $tag);
    
    /**
     * @param TagInterface $tag
     *
     * @return TagInterface
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function updateTag(TagInterface $tag);
    
    /**
     * Должен удалить полученный объект, реализующий TagInterface, и его связи
     * с статьями, и вернуть true (если удалено) или false (если неудача).
     * 
     * @param TagInterface $tag
     *
     * @return bool
     */
    public function deleteTag(TagInterface $tag);
}
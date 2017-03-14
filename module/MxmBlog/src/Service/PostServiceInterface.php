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

interface PostServiceInterface
{
    /**
     * Должен вернуть массив объектов, реализующих PostInterface или Paginator
     * 
     * @return Array of Paginator
     */
    public function findAllPosts();
    
    /**
     * Должен вернуть массив объектов, реализующих PostInterface или Paginator,
     * одной категории
     * 
     * @param CategoryInterface $category
     * 
     * @return Paginator
     */
    public function findPostsByCategory(CategoryInterface $category);
    
    /**
     * Должен вернуть массив объектов, реализующих PostInterface или Paginator,
     * одной тега
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
     * Также должен сохранять связи тег-статья в соответствующей таблице БД и тег
     * если он отсутствует в базе.
     * 
     * @param  PostInterface $post
     * @return PostInterface
     */
    public function insertPost(PostInterface $post);
    
    /**
     * Должен обновить объект, реализующий PostInterface и возвращать его же.
     * Также должен сохранять связи тег-статья в соответствующей таблице БД и тег
     * если он отсутствует в базе.
     * 
     * @param  PostInterface $post
     * @return PostInterface
     */
    public function updatePost(PostInterface $post);
    
    /**
     * Должен удалить полученный объект, реализующий PostInterface, и его связи 
     * с тегами, и вернуть true (если удалено) или false (если неудача).
     *
     * @param  PostInterface $post
     * 
     * @return bool
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
     */
    public function insertCategory(CategoryInterface $category);
    
    /**
     * Должен обновить объект, реализующий CategoryInterface и возвращать его же.
     *
     * @param  CategoryInterface $category
     * @return CategoryInterface
     */
    public function updateCategory(CategoryInterface $category);
    
    /**
     * Должен удалить полученный объект, реализующий CategoryInterface.
     *
     * @param  CategoryInterface $category
     * 
     * @return bool
     */
    public function deleteCategory(CategoryInterface $category);
    
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
     */
    public function insertTag(TagInterface $tag);
    
    /**
     * Должен обновить объект, реализующий TagInterface и возвращать его же.
     *
     * @param  TagInterface $tag
     * @return TagInterface
     */
    public function updateTag(TagInterface $tag);
    
    /**
     * Должен удалить полученный объект, реализующий TagInterface и
     * его связи со статьями.
     *
     * @param  TagInterface $tag
     * 
     * @return bool
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
     
}
<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

interface PostRepositoryInterface {

    /**
     * @param string $id
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return PostInterface
     * @throw RecordNotFoundBlogException
     * @throw InvalidArgumentBlogException
     */
    public function findPostById($id, $hideUnpublished = true);

    /**
     * @param string $name
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByName($name, $hideUnpublished = true);

    /**
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findAllPosts($hideUnpublished = true);

    /**
     * @param string $id ID категории
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByCategory($id, $hideUnpublished = true);

    /**
     * @param string $tag Название тега
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByTag($tag, $hideUnpublished = true);

    /**
     * Найти статьи, написанные определенным пользователем.
     *
     * @param string $id ID пользователя 
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByUser($id, $hideUnpublished = true);

    /**
     * Найти статьи по дате создания.
     *
     * @param string $since
     * @param string $to
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByCreateDate($since, $to, $hideUnpublished = true);

    /**
     * Найти статьи по дате редактирования.
     *
     * @param string $since
     * @param string $to
     * @param bool $hideUnpublished Скрыть неопубликованные посты. По умолчанию true.
     *
     * @return Paginator
     */
//    public function findPostsByUpdateDate($since, $to, $hideUnpublished = true);

    /**
     * Найти статьи по дате публикации.
     *
     * @param string $since
     * @param string $to
     *
     * @return Paginator
     */
//    public function findPostsByPublishDate($since, $to, $hideUnpublished = true);

    /**
     * Найти даты, когда были публикации , сгруппировать по дате и подсчитать кол-во публикаций на группу.
     *
     * @param string $group Сгруппировать по годам ('year'), месяцам ('month') или дням ('day').
     * @param string $limit Вернуть не больше, чем limit.
     * @param bool $paginated Вернуть пагинатор, если true.
     *
     * @return Laminas\Db\ResultSet|Paginator
     */
//    public function findPublishDates($group, $limit, $paginated);
}

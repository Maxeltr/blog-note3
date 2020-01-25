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

interface PostManagerInterface {

    /**
     * @param PostInterface $post The post to insert; may or may not have an identifier.
     *
     * @return PostInterface The inserted post, with identifier.
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function insertPost(PostInterface $post);

    /**
     * @param PostInterface $post The post to update; must have an identifier.
     *
     * @return PostInterface The updated post.
     * @throw InvalidArgumentBlogException
     * @throw DataBaseErrorBlogException
     */
    public function updatePost(PostInterface $post);

    /**
     * Должен удалить полученный объект, реализующий PostInterface, и его связи
     * с тегами, и вернуть true (если удалено) или false (если неудача).
     *
     * @param PostInterface $post The post to delete.
     *
     * @return bool
     */
    public function deletePost(PostInterface $post);

    /**
     * @param array|Paginator $posts
     *
     * @return int Кол-во удаленных объектов.
     */
    public function deletePosts($posts);
}

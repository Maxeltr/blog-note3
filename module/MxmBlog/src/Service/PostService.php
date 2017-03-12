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

use MxmBlog\Mapper\MapperInterface;
use MxmBlog\Model\PostInterface;
use MxmBlog\Model\CategoryInterface;
use MxmBlog\Model\TagInterface;
use MxmBlog\Service\DateTimeInterface;
use MxmBlog\Validator\IsPublishedRecordExistsValidatorInterface;

class PostService implements PostServiceInterface
{
    /**
     * @var \Blog\Mapper\MapperInterface;
     */
    protected $mapper;
    
    /**
     * @var DateTimeInterface;
     */
    protected $datetime;
    
    /**
     * @var IsPublishedValidatorInterface;
     */
    protected $isPublishedRecordExistsValidator;
    
    public function __construct(
        MapperInterface $mapper,
        DateTimeInterface $datetime,
        IsPublishedRecordExistsValidatorInterface $isPublishedValidator
    ) {
        $this->mapper = $mapper;
        $this->datetime = $datetime;
        $this->IsPublishedRecordExistsValidator = $isPublishedValidator;
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByCategory(CategoryInterface $category)
    {
        return $this->mapper->findPostsByCategory($category);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByTag(TagInterface $tag)
    {
        return $this->mapper->findPostsByTag($tag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllPosts()
    {
        return $this->mapper->findAllPosts();
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostsByPublishDate(\DateTimeInterface $since = null, \DateTimeInterface $to = null)
    {
        return $this->mapper->findPostsByPublishDate($since, $to);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPostById($id)
    {
	return $this->mapper->findPostById($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function insertPost(PostInterface $post)
    {
        $post->setCreated($this->datetime->modify('now'));
        if ($post->getIsPublished() === 1) {
            $post->setPublished($this->datetime->modify('now'));
        }
 
        $post->setVersion(1);

        return $this->mapper->insertPost($post);
    }
    
    /**
     * {@inheritDoc}
     */
    public function updatePost(PostInterface $post)
    {
        $post->setUpdated($this->datetime->modify('now'));
        
        if ($post->getIsPublished() === true && $this->IsPublishedRecordExistsValidator->isPublished() !== true) {
            $post->setPublished($this->datetime->modify('now'));
        }
        
        $post->setVersion($post->getVersion() + 1);
        
        return $this->mapper->updatePost($post);
    }
    
    /**
     * {@inheritDoc}
     */
    public function deletePost(PostInterface $post)
    {
        return $this->mapper->deletePost($post);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllCategories()
    {
        return $this->mapper->findAllCategories();
    }
    
    /**
     * {@inheritDoc}
     */
    public function findCategoryById($id)
    {
	return $this->mapper->findCategoryById($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function insertCategory(CategoryInterface $category)
    {
        return $this->mapper->insertCategory($category);
    }
    
    /**
     * {@inheritDoc}
     */
    public function updateCategory(CategoryInterface $category)
    {
        return $this->mapper->updateCategory($category);
    }
    
    /**
     * {@inheritDoc}
     */
    public function deleteCategory(CategoryInterface $category)
    {
        return $this->mapper->deleteCategory($category);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findAllTags()
    {
        return $this->mapper->findAllTags();
    }
    
    /**
     * {@inheritDoc}
     */
    public function findTagById($id)
    {
	return $this->mapper->findTagById($id);
    }
    
    /**
     * {@inheritDoc}
     */
    public function insertTag(TagInterface $tag)
    {
        return $this->mapper->insertTag($tag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function updateTag(TagInterface $tag)
    {
        return $this->mapper->updateTag($tag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function deleteTag(TagInterface $tag)
    {
        return $this->mapper->deleteTag($tag);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPublishDates($group)
    {
        return $this->mapper->findPublishDates($group, null, true);
    }
}
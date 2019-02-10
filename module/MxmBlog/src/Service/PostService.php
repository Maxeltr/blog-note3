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
use DateTimeInterface;
use MxmBlog\Validator\IsPublishedRecordExistsValidatorInterface;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Model\UserInterface;
use Zend\Validator\Db\RecordExists;
use Zend\Tag\ItemList;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Zend\Config\Config;
use Zend\Paginator\Paginator;

class PostService implements PostServiceInterface
{
    /**
     * @var string
     */
    const MISSING_KEY_ERROR = 'Invalid greeting options detected, %s array must contain %s key.';

    /**
     * @var \Blog\Mapper\MapperInterface
     */
    protected $mapper;

    /**
     * @var DateTimeInterface
     */
    protected $datetime;

    /**
     * @var IsPublishedValidatorInterface
     */
    protected $isPublishedRecordExistsValidator;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    public function __construct(
        MapperInterface $mapper,
        \DateTimeInterface $datetime,
        IsPublishedRecordExistsValidatorInterface $isPublishedValidator,
        RecordExists $isRecordExists,
        AuthorizationService $authorizationService,
        AuthenticationService $authenticationService,
        Config $config
    ) {
        $this->mapper = $mapper;
        $this->datetime = $datetime;
        $this->IsPublishedRecordExistsValidator = $isPublishedValidator;
        $this->isRecordExists = $isRecordExists;
        $this->authorizationService = $authorizationService;
        $this->authenticationService = $authenticationService;
        $this->config = $config;
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
    public function findAllPosts($hideUnpublished = true)
    {
        if ($hideUnpublished === false) {
            $this->authenticationService->checkIdentity();

            $this->authorizationService->checkPermission('find.unpublished.posts');
        }

        return $this->mapper->findAllPosts($hideUnpublished);
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
        $post = $this->mapper->findPostById($id, false);
        if ($post->getIsPublished()) {
            return $post;
        }

        $this->authorizationService->checkPermission('find.unpublished.post', $post);

	return $post;
    }

    /**
     * {@inheritDoc}
     */
    public function findPostsByUser(UserInterface $user)
    {
        if ($this->authorizationService->isGranted('find.unpublished.posts', $user)) {
            $posts = $this->mapper->findPostsByUser($user, false);
        } else {
            $posts = $this->mapper->findPostsByUser($user);
        }

	return $posts;
    }

    /**
     * {@inheritDoc}
     */
    public function findUnPublishedPostsByUser(UserInterface $user)
    {
        $this->authorizationService->checkPermission('find.unpublished.posts');

	$posts = $this->mapper->findPostsByUser($user, false);

	return $posts;
    }

    /**
     * {@inheritDoc}
     */
    public function insertPost(PostInterface $post)
    {
        $this->authorizationService->checkPermission('add.post');

        $post->setCreated($this->datetime->modify('now'));
        if ($post->getIsPublished() === true) {
            $post->setPublished($this->datetime->modify('now'));
        }

        $post->setVersion(1);

        $user = $this->authenticationService->getIdentity();
        $post->setAuthor($user);

        $this->unsetNonExistingTags($post);
        $this->unsetNonExistingCategory($post);

        return $this->mapper->insertPost($post);
    }

    /**
     * {@inheritDoc}
     */
    public function updatePost(PostInterface $post)
    {
        $this->authorizationService->checkPermission('edit.post', $post);

        $post->setUpdated($this->datetime->modify('now'));

        if ($post->getIsPublished() === true && $this->IsPublishedRecordExistsValidator->isPublished($post) !== true) {
            $post->setPublished($this->datetime->modify('now'));
        }

        $post->setVersion($post->getVersion() + 1);

        $this->unsetNonExistingTags($post);
	$this->unsetNonExistingCategory($post);

        return $this->mapper->updatePost($post);
    }

    /**
     * Unset tags that don't exist in db
     *
     * @param  PostInterface $post
     * @return void
     */
    private function unsetNonExistingTags(PostInterface $post)
    {
        $itemList = $post->getTags();

        if(!$itemList instanceof ItemList) {
            throw new InvalidArgumentBlogException(sprintf(
                'Tags property of PostInterface should contain Zend\Tag\ItemList "%s"',
                (is_object($itemList) ? get_class($itemList) : gettype($itemList))
            ));
        }

        $this->isRecordExists->setField('id');
        $this->isRecordExists->setTable('tags');

        foreach($itemList as $offset => $item) {
            if(!$item instanceof TagInterface) {
                throw new InvalidArgumentBlogException(sprintf(
                    'Itemlist of PostInterface should contain MxmBlog\Model\TagInterface "%s"',
                    (is_object($item) ? get_class($item) : gettype($item))
                ));
            }

            if(!$this->isRecordExists->isValid($item->getId())) {
                $itemList->offsetUnset($offset);
            }
        }
    }

    /**
     * Unset category that doesn't exist in db
     *
     * @param  PostInterface $post
     * @return void
     */
    private function unsetNonExistingCategory(PostInterface $post)
    {
        $category = $post->getCategory();

        if(!$category instanceof CategoryInterface) {
            throw new InvalidArgumentBlogException(sprintf(
                'Category property of PostInterface should contain CategoryInterface "%s"',
                (is_object($category) ? get_class($category) : gettype($category))
            ));
        }

        $this->isRecordExists->setField('id');
        $this->isRecordExists->setTable('category');

        if(!$this->isRecordExists->isValid($category->getId())) {
            $post->setCategory(new MxmBlog\Model\Category());		//TODO придумать что нить
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deletePost(PostInterface $post)
    {
        $this->authorizationService->checkPermission('delete.post', $post);

        return $this->mapper->deletePost($post);
    }

    /**
     * {@inheritDoc}
     */
    public function deletePosts($posts)
    {
        $this->authorizationService->checkPermission('delete.posts');

        return $this->mapper->deletePosts($posts);
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
        $this->authorizationService->checkPermission('add.category');

        return $this->mapper->insertCategory($category);
    }

    /**
     * {@inheritDoc}
     */
    public function updateCategory(CategoryInterface $category)
    {
        $this->authorizationService->checkPermission('edit.category');

        return $this->mapper->updateCategory($category);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCategory(CategoryInterface $category)
    {
        $this->authorizationService->checkPermission('delete.category');

        return $this->mapper->deleteCategory($category);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCategories($categories)
    {
        $this->authorizationService->checkPermission('delete.categories');

        if ($categories instanceof Paginator) {
            $categories = iterator_to_array($categories->setItemCountPerPage(-1));
        }

        if (! is_array($categories)) {
            throw new InvalidArgumentBlogException(sprintf(
                'The data must be array; received "%s"',
                (is_object($categories) ? get_class($categories) : gettype($categories))
            ));
        }

        if (empty($categories)) {
            throw new InvalidArgumentBlogException('The data array is empty');
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof CategoryInterface) {
                return $value->getId();
            } else {
                throw new InvalidArgumentBlogException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of CategoryInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        return $this->mapper->deleteCategories(array_map($func, $categories));
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
        $this->authorizationService->checkPermission('add.tag');

        return $this->mapper->insertTag($tag);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTag(TagInterface $tag)
    {
        $this->authorizationService->checkPermission('edit.tag');

        return $this->mapper->updateTag($tag);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTag(TagInterface $tag)
    {
        $this->authorizationService->checkPermission('delete.tag');

        return $this->mapper->deleteTag($tag);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteTags($tags)
    {
        $this->authorizationService->checkPermission('delete.tags');

        if ($tags instanceof Paginator) {
            $tags = iterator_to_array($tags->setItemCountPerPage(-1));
        }

        if (! is_array($tags)) {
            throw new InvalidArgumentBlogException(sprintf(
                'The data must be array; received "%s"',
                (is_object($tags) ? get_class($tags) : gettype($tags))
            ));
        }

        if (empty($tags)) {
            throw new InvalidArgumentBlogException('The data array is empty');
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof TagInterface) {
                return $value->getId();
            } else {
                throw new InvalidArgumentBlogException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of TagInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        return $this->mapper->deleteTags(array_map($func, $tags));
    }

    /**
     * {@inheritDoc}
     */
    public function findPublishDates($group)
    {
        return $this->mapper->findPublishDates($group, null, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getGreeting()
    {
        $options = \Zend\Config\Factory::fromFile($this->config->mxm_blog->optionFilePath);

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function editGreeting($greeting)
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('edit.greeting');

        if (! is_array($greeting)) {
            throw new InvalidArgumentBlogException(sprintf(
                'Greeting must be an array, received "%s"',
                (is_object($greeting) ? get_class($greeting) : gettype($greeting))
        ));
        }

        if (! array_key_exists('caption', $greeting)) {
            throw new InvalidArgumentBlogException(sprintf(self::MISSING_KEY_ERROR, 'greeting', 'caption'));
        }

        if (! array_key_exists('message', $greeting)) {
            throw new InvalidArgumentBlogException(sprintf(self::MISSING_KEY_ERROR, 'greeting', 'message'));
        }

        $whitelist = ['caption', 'message'];
        $greeting = array_intersect_key($greeting, array_flip($whitelist));

        $result = \Zend\Config\Factory::toFile($this->config->mxm_blog->optionFilePath, ['greeting' => $greeting]);
        if ($result === false) {
            throw new RuntimeBlogException('Unable to save greeting.');
        }

        $options = \Zend\Config\Factory::fromFile($this->config->mxm_blog->optionFilePath);

        return $options;
    }
}
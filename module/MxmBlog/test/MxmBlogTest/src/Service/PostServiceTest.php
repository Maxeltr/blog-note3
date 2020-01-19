<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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
use MxmBlog\Model\Post;
use MxmBlog\Model\Category;
use MxmBlog\Model\Tag;
use MxmBlog\Service\DateTimeInterface;
use MxmBlog\Validator\IsPublishedRecordExistsValidatorInterface;
use Laminas\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmBlog\Exception\NotAuthorizedBlogException;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmUser\Model\UserInterface;
use Laminas\Validator\Db\RecordExists;
use Laminas\Tag\ItemList;
use MxmBlog\Exception\InvalidArgumentBlogException;
use MxmUser\Model\User;
use Prophecy\Argument;

class PostServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $postService;
    protected $paginator;
    protected $authorizationService;
    protected $datetime;
    protected $mapper;
    protected $authenticationService;
    protected $isPublishedRecordExistsValidator;
    protected $isRecordExists;
    protected $resultMock;
    protected $category;
    protected $tag;
    protected $post;
    protected $user;
    protected $tagList;

    protected $traceError = true;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $array = array();
        $this->paginator = new \Laminas\Paginator\Paginator(
            new \Laminas\Paginator\Adapter\ArrayAdapter($array)
        );
        $this->user = new User();
        $this->user->setId('1');

        $this->category = new Category();
        $this->category->setId('11');

        $this->tag = new Tag(['id' => '111', 'title' => 'Code', 'weight' => 50]);

        $this->tag = new Tag(['id' => '222', 'title' => 'Zend Framework', 'weight' => 1]);


        $this->tagList = new ItemList();
        $this->tagList[] = new Tag(['id' => '1', 'title' => 'Code', 'weight' => 50]);
        $this->tagList[] = new Tag(['id' => '2', 'title' => 'Zend Framework', 'weight' => 1]);
        $this->tagList[] = new Tag(['id' => '3', 'title' => 'PHP', 'weight' => 5]);


        $this->post = new Post();
        $this->post->setId('1');
        $this->post->setIsPublished(true);
        $this->post->setCategory($this->category);
        $this->post->setTags($this->tagList);
        $this->post->setVersion(1);


        $this->mapper = $this->prophesize(MapperInterface::class);
        $this->datetime = $this->prophesize(\DateTime::class);
        $this->authService = $this->prophesize(AuthenticationService::class);
        $this->isPublishedRecordExistsValidator = $this->prophesize(IsPublishedRecordExistsValidatorInterface::class);
		$this->isRecordExists = $this->prophesize(RecordExists::class);
        $this->authorizationService = $this->prophesize(AuthorizationService::class);
        $this->resultMock = $this->prophesize(Result::class);


        $this->postService = new PostService(
            $this->mapper->reveal(),
            $this->datetime->reveal(),
            $this->isPublishedRecordExistsValidator->reveal(),
            $this->isRecordExists->reveal(),
            $this->authorizationService->reveal(),
            $this->authService->reveal()
        );

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers MxmBlog\Service\PostService::findPostsByCategory
     *
     */
    public function testFindPostsByCategory()
    {
        $this->mapper->findPostsByCategory($this->category)->willReturn($this->paginator);
        $this->assertSame($this->paginator, $this->postService->findPostsByCategory($this->category));
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostsByTag
     *
     */
    public function testFindPostsByTag()
    {
        $this->mapper->findPostsByTag($this->tag)->willReturn($this->paginator);
        $this->assertSame($this->paginator, $this->postService->findPostsByTag($this->tag));
    }

	/**
     * @covers MxmBlog\Service\PostService::findAllPosts
     *
     */
    public function testFindAllPosts()
    {
        $this->mapper->findAllPosts()->willReturn($this->paginator);
        $this->assertSame($this->paginator, $this->postService->findAllPosts());
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostsByPublishDate
     *
     */
    public function testFindPostsByPublishDate()
    {
        $this->mapper->findPostsByPublishDate($this->datetime->reveal(), $this->datetime->reveal())->willReturn($this->paginator);
        $this->assertSame($this->paginator, $this->postService->findPostsByPublishDate($this->datetime->reveal(), $this->datetime->reveal()));
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostById
     *
     */
    public function testFindPostById()
    {
        $this->mapper->findPostById('1', false)->willReturn($this->post);
        $this->assertSame($this->post, $this->postService->findPostById('1'));
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostById
     *
     */
    public function testFindPostByIdWhenPostisUnpublishedAndUserHasNotGotPermission()
    {
		$this->post->setIsPublished(false);
        $this->mapper->findPostById('1')->willReturn($this->post);
		$this->authorizationService->isGranted('find.unpublished.post', $this->post)->willReturn(false);
		$this->setExpectedException(RecordNotFoundBlogException::class, "Post with id " . $this->post->getId() . " not found");
        $this->postService->findPostById('1');
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostById
     *
     */
    public function testFindPostByIdWhenPostisUnpublishedAndUserHasGotPermission()
    {
	$this->post->setIsPublished(false);
        $this->mapper->findPostById('1')->willReturn($this->post);
	$this->authorizationService->isGranted('find.unpublished.post', $this->post)->willReturn(true);
	$this->assertSame($this->post, $this->postService->findPostById('1'));
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostsByUser
     *
     */
    public function testFindPostsByUserWhenUserIdsAreMatched()
    {
	$this->authorizationService->isGranted('find.unpublished.posts', $this->post)->willReturn(true);
        $this->mapper->findPostsByUser($this->user, false)->willReturn($this->paginator);
	$this->assertSame($this->paginator, $this->postService->findPostsByUser($this->user));
    }

	/**
     * @covers MxmBlog\Service\PostService::findPostsByUser
     *
     */
    public function testFindPostsByUserWhenUserIdsAreNotMatchedOrUserHasNotGotPermission()
    {
	$this->authorizationService->isGranted('find.unpublished.posts', $this->user)->willReturn(false);
        $this->mapper->findPostsByUser($this->user)->willReturn($this->paginator);
	$this->assertSame($this->paginator, $this->postService->findPostsByUser($this->user));
    }

	/**
     * @covers MxmBlog\Service\PostService::insertPost
     *
     */
    public function testInsertPost()
    {
		$this->authorizationService->isGranted('add.post')->willReturn(true);
		$this->datetime->modify('now')->willReturn($this->datetime->reveal());
		$this->authService->getIdentity()->willReturn($this->user);
		$this->isRecordExists->setField('id')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('tags')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('category')->willReturn($this);					//нужен ли?
		$this->isRecordExists->isValid($this->tagList[0]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[1]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[2]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->category->getId())->willReturn(true);
		$this->mapper->insertPost($this->post)->willReturn(clone $this->post);
		$this->post->setPublished($this->datetime->reveal());
		$this->assertSame($this->post, $this->postService->insertPost($this->post));
    }

	/**
     * @covers MxmBlog\Service\PostService::insertPost
     *
     */
    public function testInsertPostWhenUserHasNotGotPermission()
    {
		$this->authorizationService->isGranted('add.post')->willReturn(false);
		$this->setExpectedException(NotAuthorizedBlogException::class, 'Access denied. Permission "add.post" is required.');
		$this->postService->insertPost($this->post);
    }

	/**
     * @covers MxmBlog\Service\PostService::insertPost
     *
     */
    public function testInsertPostWhenIsPublishedPropertyIsFalse()
    {
		$this->post->setIsPublished(false);
		$this->authorizationService->isGranted('add.post')->willReturn(true);
		$this->datetime->modify('now')->willReturn($this->datetime->reveal());
		$this->authService->getIdentity()->willReturn($this->user);
		$this->isRecordExists->setField('id')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('tags')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('category')->willReturn($this);					//нужен ли?
		$this->isRecordExists->isValid($this->tagList[0]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[1]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[2]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->category->getId())->willReturn(true);
		$this->mapper->insertPost($this->post)->willReturn(clone $this->post);
		$this->assertSame($this->post, $this->postService->insertPost($this->post));
    }

	/**
     * @covers MxmBlog\Service\PostService::insertPost
     *
     */
    public function testInsertPostWithNonExistingInDbTags()
    {
		$this->authorizationService->isGranted('add.post')->willReturn(true);
		$this->datetime->modify('now')->willReturn($this->datetime->reveal());
		$this->authService->getIdentity()->willReturn($this->user);
		$this->isRecordExists->setField('id')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('tags')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('category')->willReturn($this);					//нужен ли?
		$this->isRecordExists->isValid($this->tagList[0]['id'])->willReturn(false);
		$this->isRecordExists->isValid($this->tagList[1]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[2]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->category->getId())->willReturn(true);
		$this->mapper->insertPost($this->post)->willReturn(clone $this->post);
		$this->post->getTags()->offsetUnset(0);
		$this->assertSame($this->post, $this->postService->insertPost($this->post));
    }

	/**
     * @covers MxmBlog\Service\PostService::insertPost
     *
     */
    public function testInsertPostWithNonExistingInDbCategory()
    {
		$this->authorizationService->isGranted('add.post')->willReturn(true);
		$this->datetime->modify('now')->willReturn($this->datetime->reveal());
		$this->authService->getIdentity()->willReturn($this->user);
		$this->isRecordExists->setField('id')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('tags')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('category')->willReturn($this);					//нужен ли?
		$this->isRecordExists->isValid($this->tagList[0]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[1]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[2]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->category->getId())->willReturn(false);
		$this->mapper->insertPost($this->post)->willReturn(clone $this->post);
		$this->post->setCategory(new Category());
		$this->assertSame($this->post, $this->postService->insertPost($this->post));
    }

    /**
     * @covers MxmBlog\Service\PostService::updatePost
     *
     */
    public function testUpdatePost()
    {
		$this->authorizationService->isGranted('edit.post')->willReturn(true);
		$this->datetime->modify('now')->willReturn($this->datetime->reveal());
		$this->IsPublishedRecordExistsValidator->isPublished()->willReturn(true);
		$this->isRecordExists->setField('id')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('tags')->willReturn($this);					//нужен ли?
		$this->isRecordExists->setTable('category')->willReturn($this);					//нужен ли?
		$this->isRecordExists->isValid($this->tagList[0]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[1]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->tagList[2]['id'])->willReturn(true);
		$this->isRecordExists->isValid($this->category->getId())->willReturn(true);
		$this->mapper->updatePost($this->post)->willReturn(clone $this->post);
		$this->post->setPublished($this->datetime->reveal());
		$this->assertSame($this->post, $this->postService->insertPost($this->post));
    }





}

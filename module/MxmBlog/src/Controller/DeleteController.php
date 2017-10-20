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

namespace MxmBlog\Controller;

use MxmBlog\Service\PostServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmBlog\Exception\NotAuthorizedBlogException;
use Zend\Log\Logger;

class DeleteController extends AbstractActionController
{
    /**
     * @var \Blog\Service\PostServiceInterface
     */
    protected $postService;

    /**
     *
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(PostServiceInterface $postService, Logger $logger)
    {
        $this->postService = $postService;
        $this->logger = $logger;
    }

    public function deletePostAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $post = $this->postService->findPostById($id);
        } catch (RecordNotFoundBlogException $ex) {
            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', 'no');

            if ($del === 'yes') {
                try {
                    $this->postService->deletePost($post);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                }
            }

            return $this->redirect()->toRoute('listPosts');
        }

        return new ViewModel(array(
                'post' => $post
        ));
    }

    public function deleteCategoryAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $category = $this->postService->findCategoryById($id);
        } catch (RecordNotFoundBlogException $ex) {
            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', 'no');

            if ($del === 'yes') {
                try {
                    $this->postService->deleteCategory($category);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                }
            }

            return $this->redirect()->toRoute('listCategories');
        }

        return new ViewModel(array(
                'category' => $category
        ));
    }

     public function deleteTagAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $tag = $this->postService->findTagById($id);
        } catch (RecordNotFoundBlogException $ex) {
            return $this->notFoundAction();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('delete_confirmation', 'no');

            if ($del === 'yes') {
                try {
                    $this->postService->deleteTag($tag);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                }
            }

            return $this->redirect()->toRoute('listTags');
        }

        return new ViewModel(array(
                'tag' => $tag
        ));
    }
}
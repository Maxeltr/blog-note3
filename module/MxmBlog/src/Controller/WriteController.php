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
use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Log\Logger;
use MxmBlog\Model\PostInterface;
use MxmBlog\Exception\DataBaseErrorBlogException;
use MxmBlog\Exception\NotAuthorizedBlogException;

class WriteController extends AbstractActionController
{
    /**
     *
     * @var \Blog\Service\PostServiceInterface
     */
    protected $postService;

    /**
     *
     * @var Zend\Form\FormInterface
     */
    protected $postForm;

    /**
     *
     * @var Zend\Form\FormInterface
     */
    protected $categoryForm;

    /**
     *
     * @var Zend\Form\FormInterface
     */
    protected $tagForm;

    /**
     *
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     *
     * @var Zend\Model\PostInterface
     */
    //protected $post;

    public function __construct(
        PostServiceInterface $postService,
        FormInterface $postForm,
        FormInterface $tagForm,
        FormInterface $categoryForm,
        Logger $logger
    ) {
        $this->postService = $postService;
        $this->postForm = $postForm;
        $this->tagForm = $tagForm;
        $this->categoryForm = $categoryForm;
        $this->logger = $logger;
    }

    public function addPostAction()
    {
        $error = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->postForm->setData($request->getPost());
            if ($this->postForm->isValid()) {
                try {
                    $savedPost = $this->postService->insertPost($this->postForm->getData());
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailPost',
                    array('id' => $savedPost->getId()));
            } else {
                $error = true;
            }
        }

        return new ViewModel([
            'form' => $this->postForm,
            'error' => $error
        ]);
    }

    public function editPostAction()
    {
        $request = $this->getRequest();
        try {
            $post = $this->postService->findPostById($this->params('id'));
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        $this->postForm->bind($post);
        if ($request->isPost()) {
            $this->postForm->setData($request->getPost());
            if ($this->postForm->isValid()) {
                try {
                    $this->postService->updatePost($post);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailPost',
                    array('id' => $post->getId()));
            }
        }

        return new ViewModel(array(
                'form' => $this->postForm
        ));
    }

    public function addTagAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->tagForm->setData($request->getPost());
            if ($this->tagForm->isValid()) {
                try {
                    $savedTag = $this->postService->insertTag($this->tagForm->getData());
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailTag',
                    array('id' => $savedTag->getId()));
            }
        }

        return new ViewModel(array(
            'form' => $this->tagForm
        ));
    }

    public function editTagAction()
    {
        $request = $this->getRequest();
        try {
            $tag = $this->postService->findTagById($this->params('id'));
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        $this->tagForm->bind($tag);
        if ($request->isPost()) {
            $this->tagForm->setData($request->getPost());
            if ($this->tagForm->isValid()) {
                try {
                    $this->postService->updateTag($tag);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailTag',
                    array('id' => $tag->getId()));
            }
        }

        return new ViewModel(array(
                'form' => $this->tagForm
        ));
    }

    public function addCategoryAction()
    {
        $error = false;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->categoryForm->setData($request->getPost());
            if ($this->categoryForm->isValid()) {
                try {
                    $savedCategory = $this->postService->insertCategory($this->categoryForm->getData());
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailCategory',
                    array('id' => $savedCategory->getId()));
            } else {
                $error = true;
            }
        }

        return new ViewModel(array(
            'form' => $this->categoryForm,
            'error' => $error
        ));
    }

    public function editCategoryAction()
    {
        $request = $this->getRequest();
        try {
            $category = $this->postService->findCategoryById($this->params('id'));
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        $this->categoryForm->bind($category);
        if ($request->isPost()) {
            $this->categoryForm->setData($request->getPost());
            if ($this->categoryForm->isValid()) {
                try {
                    $this->postService->updateCategory($category);
                } catch (NotAuthorizedBlogException $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

                    return $this->redirect()->toRoute('notAuthorized');
                } catch (\Exception $e) {
                    $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
                    return $this->notFoundAction();
                }

                return $this->redirect()->toRoute('detailCategory',
                    array('id' => $category->getId()));
            }
        }

        return new ViewModel(array(
                'form' => $this->categoryForm
        ));
    }
}
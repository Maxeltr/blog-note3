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

use MxmBlog\Service\DateTimeInterface;
use MxmBlog\Service\PostServiceInterface;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmUser\Exception\NotAuthenticatedUserException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Validator\Date;
use Zend\Validator\NotEmpty;
use Zend\Log\Logger;
use MxmUser\Service\UserServiceInterface;

class ListController extends AbstractActionController
{
    /**
     * @var \Blog\Service\PostServiceInterface
     */
    protected $postService;

    protected $dateValidator;

    protected $datetime;

    protected $digitsFilter;

    protected $notEmptyValidator;

    protected $dateTimeFormat;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     *
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \User\Service\UserServiceInterface
     */
    protected $userService;

    public function __construct(
        PostServiceInterface $postService,
        Date $dateValidator,
        DateTimeInterface $datetime,
        Config $config,
        NotEmpty $notEmptyValidator,
        Logger $logger,
        UserServiceInterface $userService
    ) {
        $this->postService = $postService;
        $this->dateValidator = $dateValidator;
        $this->datetime = $datetime;
        $this->config = $config;
        $this->notEmptyValidator = $notEmptyValidator;
        $this->dateTimeFormat = $this->config->dateTime->dateTimeFormat;
        $this->logger = $logger;
        $this->userService = $userService;
    }

    public function indexAction()
    {
        $paginator = $this->postService->findAllPosts();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'posts' => $paginator,
            'route' => 'listPosts'
        ]);
    }

    public function listPostsAction()
    {
        $paginator = $this->postService->findAllPosts();
        $this->configurePaginator($paginator);

        return new ViewModel([
            'posts' => $paginator,
            'route' => 'listPosts'
        ]);
    }

    public function listPostsByCategoryAction()
    {
        $categoryId = $this->params()->fromRoute('id');

        try {
            $category = $this->postService->findCategoryById($categoryId);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        $paginator = $this->postService->findPostsByCategory($category);
        $this->configurePaginator($paginator);

        $model = new ViewModel([
            'posts' => $paginator,
            'route' => 'listPostsByCategory'
        ]);
        $model->setTemplate('mxm-blog/list/list-posts');

        return $model;
    }

    public function listPostsByTagAction()
    {
        $tagId = $this->params()->fromRoute('id');

        try {
            $tag = $this->postService->findTagById($tagId);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        $paginator = $this->postService->findPostsByTag($tag);
        $this->configurePaginator($paginator);

        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'listPostsByTag'
        ));
        $model->setTemplate('mxm-blog/list/list-posts');

        return $model;
    }

    public function listPostsByUserAction()
    {
        $userId = $this->params()->fromRoute('id');

        try {
            $user = $this->userService->findUserById($userId);
        } catch (NotAuthenticatedUserException $e) {
            $redirectUrl = $this->url()->fromRoute('listPostsByUser', ['id' => $userId, 'page' => (int) $this->params()->fromRoute('page', '1')]);

            return $this->redirect()->toRoute('loginUser', [], ['query' => ['redirect' => $redirectUrl]]);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            
            return $this->notFoundAction();
        }

        $paginator = $this->postService->findPostsByUser($user);
        $this->configurePaginator($paginator);

        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'listPostsByUser'
        ));
        $model->setTemplate('mxm-blog/list/list-posts');

        return $model;
    }

    public function listPostsByPublishedAction()
    {
        $since = $this->params()->fromRoute('since');
        $to = $this->params()->fromRoute('to');

        $since = $since . ' 00:00:00';
        $to = $to . ' 23:59:59';

        if (!$this->dateValidator->isValid($since)) {
            return $this->notFoundAction();
        }
        $since = $this->datetime->createFromFormat($this->dateTimeFormat, $since);

        if (!$this->dateValidator->isValid($to)) {
            return $this->notFoundAction();
        }
        $to = $this->datetime->createFromFormat($this->dateTimeFormat, $to);

        $paginator = $this->postService->findPostsByPublishDate($since, $to);
        $this->configurePaginator($paginator);

        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'listPostsByPublished'
        ));
        $model->setTemplate('mxm-blog/list/list-posts');

        return $model;
    }

    public function listArchivesPostsAction()
    {
        $year = $this->params()->fromRoute('year');
        $month = $this->params()->fromRoute('month');
        $day = $this->params()->fromRoute('day');

        $period = $this->createPeriod($year, $month, $day);
        if ($period === false) {
            return $this->notFoundAction();
        }

        $paginator = $this->postService->findPostsByPublishDate($period['since'], $period['to']);
        $this->configurePaginator($paginator);

        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'listArchivesPosts'
        ));
        $model->setTemplate('mxm-blog/list/list-posts');

        return $model;
    }

    /**
     * Формирует из года, месяца, дня определенный период
     * времени (год, месяц или день). Возвращает массив из DateTime'ов.
     *
     * @param string $year
     * @param string $month
     * @param string $day
     *
     * @return mixed
     */
    private function createPeriod($year, $month, $day)
    {
        if (!$this->notEmptyValidator->isValid($year)) {
            return false;
        }

        if ($this->notEmptyValidator->isValid($month)) {
            if ($this->notEmptyValidator->isValid($day)) {
                $since = $year . '-' . $month . '-' . $day . ' 00:00:00';
                $interval = \DateInterval::createFromDateString('23 hours + 59 minutes + 59 seconds');
            } else {
                $since = $year . '-' . $month . '-01 00:00:00';
                $interval = \DateInterval::createFromDateString('1 month - 1 day + 23 hours + 59 minutes + 59 seconds');
            }
        } else {
            $since = $year . '-01-01 00:00:00';
            $interval = \DateInterval::createFromDateString('1 year - 1 day + 23 hours + 59 minutes + 59 seconds');
        }

        if (!$this->dateValidator->isValid($since)) {
            return false;
        }
        $since = $this->datetime->createFromFormat($this->dateTimeFormat, $since);
        $to = $since->add( $interval );

        return ['since' => $since, 'to' => $to];
    }

    public function listArchivesAction()
    {
        $paginator = $this->postService->findPublishDates('day');
        $this->configurePaginator($paginator);

        $model = new ViewModel(array(
            'archives' => $paginator,
            'route' => 'listArchives'
        ));
        $model->setTemplate('mxm-blog/list/list-archives');

        return $model;
    }

    public function detailPostAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $post = $this->postService->findPostById($id);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'post' => $post
        ));
    }

    public function listCategoriesAction()
    {
        $paginator = $this->postService->findAllCategories();
        $this->configurePaginator($paginator);

        return new ViewModel(['categories' => $paginator]);
    }

    public function detailCategoryAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $category = $this->postService->findCategoryById($id);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'category' => $category
        ));
    }

    public function listTagsAction()
    {
        $paginator = $this->postService->findAllTags();
        $this->configurePaginator($paginator);

        return new ViewModel(['tags' => $paginator]);
    }

    public function detailTagAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $tag = $this->postService->findTagById($id);
        } catch (\Exception $e) {
            $this->logger->err($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());
            return $this->notFoundAction();
        }

        return new ViewModel(array(
            'tag' => $tag
        ));
    }

    private function configurePaginator(Paginator $paginator)
    {
        $page = (int) $this->params()->fromRoute('page');
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($this->config->listController->ItemCountPerPage);

        return $this;
    }
}

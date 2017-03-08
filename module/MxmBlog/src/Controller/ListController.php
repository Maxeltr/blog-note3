<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace MxmBlog\Controller;

use MxmBlog\Service\DateTimeInterface;
use MxmBlog\Service\PostServiceInterface;
use MxmBlog\Exception\RecordNotFoundBlogException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Validator\Date;
use Zend\Filter\Digits;

class ListController extends AbstractActionController
{
    /**
     * @var \Blog\Service\PostServiceInterface
     */
    protected $postService;
    
    protected $dateValidator;
    
    protected $datetime;
    
    protected $digitsFilter;

    /**
     * @var Zend\Config\Config
     */
    protected $config;
    
    public function __construct(
        PostServiceInterface $postService, 
        Date $dateValidator, 
        DateTimeInterface $datetime, 
        Config $config,
        Digits $digitsFilter
    ) {
        $this->postService = $postService;
        $this->dateValidator = $dateValidator;
        $this->datetime = $datetime;
        $this->config = $config;
        $this->digitsFilter = $digitsFilter;
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
        } catch (\InvalidArgumentException $ex) {
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
        } catch (\InvalidArgumentException $ex) {
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
    
    public function listPostsByPublishedAction()
    {
        $since = $this->params()->fromRoute('since');
        $to = $this->params()->fromRoute('to');

        $since = $since . ' 00:00:00';
        $to = $to . ' 23:59:59';
        
        $dateTimeFormat = $this->config->dateTime->dateTimeFormat;
        $this->dateValidator->setFormat($dateTimeFormat);
        
        if (!$this->dateValidator->isValid($since)) {
            return $this->notFoundAction();
        } else {
            $since = $this->datetime->createFromFormat($dateTimeFormat, $since);
        }
        
        if (!$this->dateValidator->isValid($to)) {
            return $this->notFoundAction();
        } else {
            $to = $this->datetime->createFromFormat($dateTimeFormat, $to);
        }
        
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

        $year = $this->digitsFilter->filter($year);
        if (!$year) {
            return $this->notFoundAction();
        }
        
        $dateTimeFormat = $this->config->dateTime->dateTimeFormat;
        $this->dateValidator->setFormat($dateTimeFormat);
        
        $month = $this->digitsFilter->filter($month);
        if (!$month) {
            $since = $year . '-01-01 00:00:00';
            if (!$this->dateValidator->isValid($since)) {
                return $this->notFoundAction();
            } else {
                $since = $this->datetime->createFromFormat($dateTimeFormat, $since);
            }
            $interval = \DateInterval::createFromDateString('1 year - 1 day');
            $to = $since->add( $interval );
        } else {
            $since = $year . '-' . $month . '-01 00:00:00';
            if (!$this->dateValidator->isValid($since)) {
                return $this->notFoundAction();
            } else {
                $since = $this->datetime->createFromFormat($dateTimeFormat, $since);
            }
            $to = $since->modify( 'last day of this month' );
        }
                
        $paginator = $this->postService->findPostsByPublishDate($since, $to);
        $this->configurePaginator($paginator);
        
        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'listArchivesPosts'
        ));
        $model->setTemplate('mxm-blog/list/list-posts');
        
        return $model;
    }
    
    public function detailPostAction()
    {
        $id = $this->params()->fromRoute('id');
        try {
            $post = $this->postService->findPostById($id);
        } catch (RecordNotFoundBlogException $ex) {
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
        } catch (\InvalidArgumentException $ex) {
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
        } catch (\InvalidArgumentException $ex) {
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

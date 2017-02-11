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


class IndexController extends AbstractActionController
{
    /**
     * @var \Blog\Service\PostServiceInterface
     */
    protected $postService;
    
    protected $dateValidator;
    
    protected $datetime;


    /**
     * @var Zend\Config\Config
     */
    protected $config;
    
    public function __construct(
        PostServiceInterface $postService, 
        Date $dateValidator, 
        DateTimeInterface $datetime, 
        Config $config
    ) {
        $this->postService = $postService;
        $this->dateValidator = $dateValidator;
        $this->datetime = $datetime;
        $this->config = $config;
    }

    public function indexAction()
    {
        $paginator = $this->postService->findAllPosts();
        $this->configurePaginator($paginator);
        
        foreach ($paginator as $qw) {
            \Zend\Debug\Debug::dump($qw);
        }
        
        die("IndexController");
                
        return new ViewModel([
            'posts' => $paginator,
            'route' => 'blog/page'
        ]);
    }
    
    public function listPostsAction()
    {
        $paginator = $this->postService->findAllPosts();
        $this->configurePaginator($paginator);
        
        return new ViewModel([
            'posts' => $paginator,
            'route' => 'blog/listPosts'
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
            'route' => 'blog/listPostsByCategory'
        ]);
        $model->setTemplate('blog/list/list-posts');
        
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
            'route' => 'blog/listPostsByTag'
        ));
        $model->setTemplate('blog/list/list-posts');
        
        return $model;
    }
    
    public function listPostsByPublishedAction()
    {
        $since = $this->params()->fromRoute('since');
        $to = $this->params()->fromRoute('to');
        
        $since . ' 00:00:00';
        $to . ' 23:59:59';
        
        $dateTimeFormat = $this->config->dateTime->dateTimeFormat;
        $this->dateValidator->setFormat($dateTimeFormat);
                
        if ($this->dateValidator->isValid($since)) {
            $since = $this->datetime->createFromFormat(dateTimeFormat, $since);
        }
        
        if ($this->dateValidator->isValid($to)) {
            $to = $this->datetime->createFromFormat(dateTimeFormat, $to);
        }

        $paginator = $this->postService->findPostsByPublishDate($since, $to);
        $this->configurePaginator($paginator);
        
        $model = new ViewModel(array(
            'posts' => $paginator,
            'route' => 'blog/listPostsByPublished'
        ));
        $model->setTemplate('blog/list/list-posts');
        
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

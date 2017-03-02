<?php
namespace MxmBlog;

use Zend\ServiceManager\Factory\InvokableFactory;

use Zend\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Factory\Controller\IndexControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\PostServiceInterface::class => Factory\Service\PostServiceFactory::class,
            Service\DateTimeInterface::class => Factory\Service\DateTimeFactory::class,
            Validator\IsPublishedRecordExistsValidatorInterface::class => Factory\Validator\IsPublishedRecordExistsValidatorFactory::class,
            Hydrator\Post\TagsHydrator::class => Factory\Hydrator\TagsHydratorFactory::class,
            Hydrator\Post\CategoryHydrator::class => Factory\Hydrator\CategoryHydratorFactory::class,
            Hydrator\Post\PostHydrator::class => Factory\Hydrator\PostHydratorFactory::class,
            Hydrator\Post\DatesHydrator::class => Factory\Hydrator\DatesHydratorFactory::class,
            Mapper\MapperInterface::class => Factory\Mapper\ZendDbSqlMapperFactory::class,
            Model\PostInterface::class => Factory\Model\PostFactory::class,
            Model\TagInterface::class => Factory\Model\TagFactory::class,
            Model\CategoryInterface::class => Factory\Model\CategoryFactory::class,
            \Zend\Hydrator\Aggregate\AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            \Zend\Validator\Date::class => Factory\Validator\DateValidatorFactory::class,
        ],
        'invokables' => [
            Hydrator\Tag\TagHydrator::class => Hydrator\Tag\TagHydrator::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'archiveDates' => View\Helper\ArchiveDates::class,
            'FormatDateI18n' => View\Helper\FormatDateI18nFactory::class,
        ],
        'factories' => [
            View\Helper\ArchiveDates::class => Factory\View\Helper\ArchiveDatesFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type'    => 'Literal',
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    
                ],
            ],
            'detailPost' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/post/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action' => 'detailPost'
                    ],
                ],
            ],
            'listPosts' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/posts[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action' => 'listPosts'
                    ],
                ],
            ],
            'listArchivesPosts' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/archives/posts[/:year[/:month[/:page]]]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                        'year' => '[1-9]\d*',
                        'month' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action' => 'listArchivesPosts'
                    ],
                ],
            ],
            'listTags' => [    //это название роута используется в контроллерах представлениях
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/tags[/:page]', //это будет показываться в адресной строке
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action' => 'listTags'
                    ],
                ],
            ],
            'listCategories' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/categories[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action' => 'listCategories'
                    ],
                ],
            ],
            'addPost' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/add/post',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addPost'
                    ],
                ],
            ],
            'addCategory' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/add/category',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addCategory'
                    ],
                ],
            ],
            'addTag' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/add/tag',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addTag'
                    ],
                ],
            ],
            
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmBlog' => __DIR__ . '/../view',
        ],
    ],
];

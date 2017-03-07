<?php
namespace MxmBlog;

use Zend\ServiceManager\Factory\InvokableFactory;

use Zend\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\Controller\ListControllerFactory::class,
            Controller\WriteController::class => Factory\Controller\WriteControllerFactory::class,
            Controller\DeleteController::class => Factory\Controller\DeleteControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\PostServiceInterface::class => Service\PostService::class,
            Service\DateTimeInterface::class => Service\DateTime::class,
            Validator\IsPublishedRecordExistsValidatorInterface::class => Validator\IsPublishedRecordExistsValidator::class,
            Mapper\MapperInterface::class => Mapper\ZendDbSqlMapper::class,
            Model\PostInterface::class => Model\Post::class,
            Model\TagInterface::class => Model\Tag::class,
            Model\CategoryInterface::class => Model\Category::class,
        ],
        'factories' => [
            Service\PostService::class => Factory\Service\PostServiceFactory::class,
            Service\DateTime::class => Factory\Service\DateTimeFactory::class,
            Validator\IsPublishedRecordExistsValidator::class => Factory\Validator\IsPublishedRecordExistsValidatorFactory::class,
            Hydrator\Post\TagsHydrator::class => Factory\Hydrator\TagsHydratorFactory::class,
            Hydrator\Post\CategoryHydrator::class => Factory\Hydrator\CategoryHydratorFactory::class,
            Hydrator\Post\PostHydrator::class => Factory\Hydrator\PostHydratorFactory::class,
            Hydrator\Post\DatesHydrator::class => Factory\Hydrator\DatesHydratorFactory::class,
            Mapper\ZendDbSqlMapper::class => Factory\Mapper\ZendDbSqlMapperFactory::class,
            Model\Post::class => Factory\Model\PostFactory::class,
            Model\Tag::class => Factory\Model\TagFactory::class,
            Model\Category::class => Factory\Model\CategoryFactory::class,
            \Zend\Hydrator\Aggregate\AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            \Zend\Validator\Date::class => Factory\Validator\DateValidatorFactory::class,
            \Zend\I18n\Translator\TranslatorInterface::class => \Zend\I18n\Translator\TranslatorServiceFactory::class,
            //Soflomo\Purifier\PurifierFilter::class => Soflomo\Purifier\Factory\PurifierFilterFactory::class,
        ],
        'invokables' => [
            Hydrator\Tag\TagHydrator::class => Hydrator\Tag\TagHydrator::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'archiveDates' => View\Helper\ArchiveDates::class,
            'formatDateI18n' => View\Helper\FormatDateI18n::class,
        ],
        'factories' => [
            View\Helper\ArchiveDates::class => Factory\View\Helper\ArchiveDatesFactory::class,
            View\Helper\FormatDateI18n::class => Factory\View\Helper\FormatDateI18nFactory::class,
        ],
        'invokables' => [
            'translate' => \Zend\I18n\View\Helper\Translate::class
        ]
    ],
    'filters' => [
        'aliases' => [
            //'htmlpurifier' => Soflomo\Purifier\PurifierFilter::class,
        ],
        'factories' => [
            //Soflomo\Purifier\PurifierFilter::class => Soflomo\Purifier\Factory\PurifierFilterFactory::class,
        ],
        'invokables' => [
            
        ]
    ],
    'form_elements' => [
        'factories' => [
            Form\PostForm::class => Factory\Form\PostFormFactory::class,
            Form\PostFieldset::class => Factory\Form\PostFieldsetFactory::class,
            Form\CategoriesFieldset::class => Factory\Form\CategoriesFieldsetFactory::class,
            Form\TagFieldset::class => Factory\Form\TagFieldsetFactory::class,
            Form\CategoryForm::class => Factory\Form\CategoryFormFactory::class,
            Form\CategoryFieldset::class => Factory\Form\CategoryFieldsetFactory::class,
            Form\TagForm::class => Factory\Form\TagFormFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type'    => 'Literal',
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/',
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
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
                        'controller'    => Controller\ListController::class,
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
                        'controller'    => Controller\ListController::class,
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
                        'controller'    => Controller\ListController::class,
                        'action' => 'listArchivesPosts'
                    ],
                ],
            ],
            'listPostsByCategory' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/posts/category[/:id[/:page]]',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listPostsByCategory'
                    ],
                ],
            ],
            'listPostsByTag' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/posts/tag[/:id[/:page]]',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listPostsByTag'
                    ],
                ],
            ],
            'editPost' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/edit/post/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'editPost'
                    ],
                ],
            ],
            'detailTag' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/tag/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'detailTag'
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
                        'controller'    => Controller\ListController::class,
                        'action' => 'listTags'
                    ],
                ],
            ],
            'detailCategory' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/category/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'detailCategory'
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
                        'controller'    => Controller\ListController::class,
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
            'editCategory' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/edit/category/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'editCategory'
                    ],
                ],
            ],
            'editTag' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/edit/tag/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'editTag'
                    ],
                ],
            ],
            'deletePost' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete/post/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\DeleteController::class,
                        'action' => 'deletePost'
                    ],
                ],
            ],
            'deleteTag' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete/tag/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\DeleteController::class,
                        'action' => 'deleteTag'
                    ],
                ],
            ],
            'deleteCategory' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete/category/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\DeleteController::class,
                        'action' => 'deleteCategory'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmBlog' => __DIR__ . '/../view',
        ],
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml'
        ],
    ],
];

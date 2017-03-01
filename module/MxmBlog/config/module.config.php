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
                        'controller'    => Controller\Index::class,
                        'action' => 'detailPost'
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

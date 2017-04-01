<?php
namespace MxmUser;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;

return [
    'user_module' => [
        'listController' => [
            'ItemCountPerPage' => 10,
        ],
        'dateTime' => [
            'timezone' => 'Europe/Moscow',  //зона по умолчанию для создания дефолтных DateTime
            'locale' => 'ru_RU',
            'dateTimeFormat' => 'Y-m-d H:i:s', //TODO По моему эта херня жестко закодена в контроллере. Если здесь изменить то не будет работать?
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmUser.log',
        ],
        
    ],
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\Controller\ListControllerFactory::class,
            Controller\WriteController::class => Factory\Controller\WriteControllerFactory::class,
            Controller\DeleteController::class => Factory\Controller\DeleteControllerFactory::class,
            Controller\AuthController::class => Factory\Controller\AuthControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\UserServiceInterface::class => Service\UserService::class,
            Service\DateTimeInterface::class => Service\DateTime::class,
            Mapper\MapperInterface::class => Mapper\ZendDbSqlMapper::class,
            Model\UserInterface::class => Model\User::class,
            \Zend\Authentication\AuthenticationService::class => AuthenticationService::class,
        ],
        'factories' => [
            Service\UserService::class => Factory\Service\UserServiceFactory::class,
            Service\DateTime::class => Factory\Service\DateTimeFactory::class,
            Mapper\ZendDbSqlMapper::class => Factory\Mapper\ZendDbSqlMapperFactory::class,
            Model\User::class => Factory\Model\UserFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            Hydrator\User\UserHydrator::class => Factory\Hydrator\UserHydratorFactory::class,
            Hydrator\User\DatesHydrator::class => Factory\Hydrator\DatesHydratorFactory::class,
            Hydrator\User\TimebeltHydrator::class => InvokableFactory::class,
            Hydrator\Timezone\TimezoneHydrator::class => InvokableFactory::class,
            AuthenticationService::class => Factory\Service\AuthServiceFactory::class,
            Service\Authentication\Adapter\AuthAdapter::class => Factory\Service\AuthAdapterFactory::class,
            //Zend\Hydrator\Aggregate\AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            AggregateHydrator::class => Factory\Hydrator\AggregateHydratorFactory::class,
            Date::class => Factory\Validator\DateValidatorFactory::class,
            //Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            Logger::class => Factory\Logger\LoggerFactory::class,
            
        ],
        'invokables' => [
            //Hydrator\TimezoneHydrator::class => InvokableFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\EditUserForm::class => Factory\Form\EditUserFormFactory::class,
            Form\EditUserFieldset::class => Factory\Form\EditUserFieldsetFactory::class,
            Form\TimebeltFieldset::class => Factory\Form\TimebeltFieldsetFactory::class,
            Form\RegisterUserForm::class => Factory\Form\RegisterUserFormFactory::class,
            Form\RegisterUserFieldset::class => Factory\Form\RegisterUserFieldsetFactory::class,
            Form\ChangeEmailForm::class => Factory\Form\ChangeEmailFormFactory::class, 
            Form\ChangePasswordForm::class => Factory\Form\ChangePasswordFormFactory::class,
            Form\LoginUserForm::class => Factory\Form\LoginUserFormFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            'login' => [
                'type'    => 'Literal',
                'options' => [
                    // Change this to something specific to your module
                    'route'    => '/login',
                    'defaults' => [
                        'controller'    => Controller\AuthController::class,
                        'action'        => 'login',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    // You can place additional routes that match under the
                    // route defined above here.
                ],
            ],
            'listUsers' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/users[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listUsers'
                    ],
                ],
            ],
            'detailUser' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/user/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'detailUser'
                    ],
                ],
            ],
            'addUser' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/add/user',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addUser'
                    ],
                ],
            ],
            'editUser' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/edit/user/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'editUser'
                    ],
                ],
            ],
            'changePassword' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/change/password',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'changePassword',
                    ],
                ],
            ],
            'loginUser' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'loginUser',
                    ],
                ],
            ],
            'logoutUser' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'logoutUser',
                    ],
                ],
            ],
            'changeEmail' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/change/email',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'changeEmail',
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmUser' => __DIR__ . '/../view',
        ],
    ],
    // Session configuration.
    'session_config' => [
        'cookie_lifetime'     => 60*60*1, // Session cookie will expire in 1 hour.
        'gc_maxlifetime'      => 60*60*24*30, // How long to store session data on server (for 1 month).        
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
];

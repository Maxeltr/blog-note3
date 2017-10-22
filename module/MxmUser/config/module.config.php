<?php
namespace MxmUser;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;

return [
    'mxm_user' => [
        'listController' => [
            'ItemCountPerPage' => 10,
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmUser.log',
        ],

    ],
    'defaults' => [
	'locale' => 'ru',
	'timezone' => 'Europe/Moscow',
	'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
    'translator' => array(
        'locale' => 'en',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../languages',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\Controller\ListControllerFactory::class,
            Controller\WriteController::class => Factory\Controller\WriteControllerFactory::class,
            Controller\DeleteController::class => Factory\Controller\DeleteControllerFactory::class,
            Controller\AuthenticateController::class => Factory\Controller\AuthenticateControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\UserServiceInterface::class => Service\UserService::class,
            Service\DateTimeInterface::class => Service\DateTime::class,
            Mapper\MapperInterface::class => Mapper\ZendDbSqlMapper::class,
            Model\UserInterface::class => Model\User::class,
            \Zend\Authentication\AuthenticationService::class => AuthenticationService::class,
            \Zend\i18n\Translator\TranslatorInterface::class => Zend\I18n\Translator\Translator::class,
            //\Zend\Validator\Translator\TranslatorInterface::class => Zend\Mvc\I18n\Translator::class


        ],
        'factories' => [
            Service\UserService::class => Factory\Service\UserServiceFactory::class,
            Service\DateTime::class => Factory\Service\DateTimeFactory::class,
            Mapper\ZendDbSqlMapper::class => Factory\Mapper\ZendDbSqlMapperFactory::class,
            Model\User::class => Factory\Model\UserFactory::class,
            \Zend\Db\Adapter\Adapter::class => \Zend\Db\Adapter\AdapterServiceFactory::class,
            Hydrator\TimezoneFormHydrator\TimezoneFormHydrator::class => Factory\Hydrator\TimezoneFormHydratorFactory::class,
            AuthenticationService::class => Factory\Service\AuthenticationServiceFactory::class,
            Service\Authentication\Adapter\AuthAdapter::class => Factory\Service\AuthAdapterFactory::class,
            Hydrator\UserMapperHydrator\UserMapperHydrator::class => Factory\Hydrator\UserMapperHydratorFactory::class,
            Hydrator\UserFormHydrator\UserFormHydrator::class => Factory\Hydrator\UserFormHydratorFactory::class,
            Date::class => Factory\Validator\DateValidatorFactory::class,
            Logger::class => Factory\Logger\LoggerFactory::class,
            Zend\I18n\Translator\Translator::class => \Zend\I18n\Translator\TranslatorServiceFactory::class,
            //Zend\Mvc\I18n\Translator::class => \Zend\Mvc\I18n\TranslatorFactory::class
        ],
        'delegators' => [
            Zend\I18n\Translator\Translator::class => [
                Translator\TranslatorDelegator::class
            ],
            \Zend\Mvc\I18n\Translator::class => [
                Translator\MvcTranslatorDelegator::class
            ],
	],
        'invokables' => [

        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\EditUserForm::class => Factory\Form\EditUserFormFactory::class,
            Form\EditUserFieldset::class => Factory\Form\EditUserFieldsetFactory::class,
            Form\TimebeltFieldset::class => Factory\Form\TimebeltFieldsetFactory::class,
            Form\RegisterUserForm::class => Factory\Form\RegisterUserFormFactory::class,
            Form\RegisterUserFieldset::class => Factory\Form\RegisterUserFieldsetFactory::class,
            Form\EditEmailForm::class => Factory\Form\EditEmailFormFactory::class,
            Form\EditPasswordForm::class => Factory\Form\EditPasswordFormFactory::class,
            Form\LoginUserForm::class => Factory\Form\LoginUserFormFactory::class,
            Form\ResetPasswordForm::class => Factory\Form\ResetPasswordFormFactory::class,
            Form\SetPasswordForm::class => Factory\Form\SetPasswordFormFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
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
            'deleteUser' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/delete/user/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\DeleteController::class,
                        'action' => 'deleteUser'
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
            'editPassword' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/edit/password',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'editPassword',
                    ],
                ],
            ],
            'resetPassword' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/reset/password',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'resetPassword',
                    ],
                ],
            ],
            'setPassword' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/set/password/:token',
                    'constraints' => [
                        'token' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'setPassword',
                    ],
                ],
            ],
            'confirmEmail' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/confirm/email/:token',
                    'constraints' => [
                        'token' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'confirmEmail',
                    ],
                ],
            ],
            'loginUser' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthenticateController::class,
                        'action'     => 'loginUser',
                    ],
                ],
            ],
            'logoutUser' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthenticateController::class,
                        'action'     => 'logoutUser',
                    ],
                ],
            ],
            'editEmail' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/edit/email',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action'     => 'editEmail',
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
    // Настройки сессии. Если приложение не использует сессии, то модуль MxmUSer использует данные настройки.
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
    'session_containers' => [							//
        'MxmUserSessionContainer'
    ],
];

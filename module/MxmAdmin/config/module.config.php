<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmAdmin;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'mxm_admin' => [
        'adminController' => [
            'ItemCountPerPage' => 25,
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmAdmin.log',
        ],
        'logs' => [
            'path' => __DIR__ . '/../../../data/logs/',
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\AdminController::class => Controller\AdminControllerFactory::class
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            \Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
        ],
        'aliases' => [
            Service\AdminServiceInterface::class => Service\AdminService::class,
        ],
        'factories' => [
            Logger::class => Logger\LoggerFactory::class,
            Service\AdminService::class => Service\AdminServiceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'manageFiles' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/files[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageFiles',
                    ],
                ],
            ],
            'manageUsers' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/users[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageUsers',
                    ],
                ],
            ],
            'manageClients' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/clients[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageClients',
                    ],
                ],
            ],
            'managePosts' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/posts[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'managePosts',
                    ],
                ],
            ],
            'manageCategories' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/categories[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageCategories',
                    ],
                ],
            ],
            'manageTags' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/tags[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageTags',
                    ],
                ],
            ],
            'manageLogs' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/manage/logs[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageLogs',
                    ],
                ],
            ],
            'downloadLogFile' => [
                'type' => Segment::class,
                'options' => [
                    'route'    => '/download/log[/:file]',
                    'constraints' => [
                        'file' => '[a-zA-Z][a-zA-Z0-9._-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'downloadLogFile',
                    ],
                ],
            ],
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmAdmin' => __DIR__ . '/../view',
        ],
    ],
    'navigation' => [
        'adminSidebar' => [
            [
                'label' => 'Files',
                'route' => 'manageFiles',
            ],
            [
                'label' => 'Logs',
                'route' => 'manageLogs',
            ],
            [
                'label' => 'Users',
                'route' => 'manageUsers',
            ],
            [
                'label' => 'Clients',
                'route' => 'manageClients',
            ],
            [
                'label' => 'Posts',
                'route' => 'managePosts',
            ],
            [
                'label' => 'Categories',
                'route' => 'manageCategories',
            ],
            [
                'label' => 'Tags',
                'route' => 'manageTags',
            ],
        ],
    ],
];
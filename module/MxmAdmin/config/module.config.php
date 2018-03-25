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

return [
    'mxm_admin' => [
        'adminController' => [
            'ItemCountPerPage' => 25,
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmAdmin.log',
        ],
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
        'factories' => [
            Logger::class => Logger\LoggerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'manageFiles' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/files',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageFiles',
                    ],
                ],
            ],
            'manageUsers' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/users',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'manageUsers',
                    ],
                ],
            ],
            'manageClients' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/clients',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'managePosts' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/posts',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'manageCategories' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/categories',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'manageTags' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/tags',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'manageLogs' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage/logs',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
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
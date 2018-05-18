<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

namespace MxmRbac;

return [
    'controllers' => [
        'factories' => [
            Controller\AuthorizeController::class => Factory\Controller\AuthorizeControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\AuthorizationServiceInterface::class => Service\AuthorizationService::class,
            Guard\RouteGuardInterface::class => Guard\RouteGuard::class,
        ],
        'factories' => [
            Service\AuthorizationService::class => Factory\Service\AuthorizationServiceFactory::class,
            Assertion\AssertionPluginManager::class => Factory\Assertion\AssertionPluginManagerFactory::class,
            Logger::class => Factory\Logger\LoggerFactory::class,
            Guard\RouteGuard::class => Factory\Guard\RouteGuardFactory::class,
        ],
        'invokables' => [
            //Assertion\MustBeAuthorAssertion::class => Assertion\MustBeAuthorAssertion::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'isGranted' => View\Helper\IsGranted::class,
            'hasRole' => View\Helper\HasRole::class,
        ],
        'factories' => [
            View\Helper\IsGranted::class => Factory\View\Helper\IsGrantedFactory::class,
            View\Helper\HasRole::class => Factory\View\Helper\HasRoleFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'notAuthorized' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/not-authorized',
                    'defaults' => [
                        'controller' => Controller\AuthorizeController::class,
                        'action'     => 'notAuthorized',
                    ],
                ],
            ],
        ]
    ],
    'rbac_module' => [      //TODO rename to mxm_rbac
        'rbac_config' => [
            'roles' => [
                'admin' => [
                    'parent' => '',
                    'no_assertion' => true,
                    'permissions' => [
                        'edit.options',
                        'change.roles',
                        'add.client.rest',
                        //'find.client.rest',
                        'delete.client.rest',
                        'revoke.token.rest',
                        //'find.clients.rest',
                        'fetch.file.rest',
                        'fetch.files.rest',
                        'delete.file.rest',
                        'create.file.rest',
                        'find.logs',
                        'download.log',
                        'edit.greeting',
                        'delete.categories',
                    ]
                ],
                'moderator' => [
                    'parent' => 'admin',
                    //'no_assertion' => true,
                    'permissions' => [
                        'edit.category',
                        'delete.category',
                        'edit.tag',
                        'delete.tag',
                        'find.users'
                    ]
                ],
                'author' => [
                    'parent' => 'moderator',
                    'permissions' => [
                        'add.post',
                        'edit.post',
                        'delete.post',
                        'find.unpublished.post',
                        'find.unpublished.posts',
                        'add.category',
			'add.tag',

                    ],
                ],
                'user' => [
                    'parent' => 'author',
                    'permissions' => [
                        'find.user',
                        'edit.user',
                        'delete.user',
			'edit.password',
			'edit.email',
                        'find.clients.rest',
                        'find.client.rest',
                    ]
                ],
            ],
            'assertions' => [       //TODO проверить все модули как себя ведут если удалить настройки
                'MustBeAuthorAssertion' => [
                    'permissions' => [
                        'edit.post',
                        'delete.post',
                        'find.unpublished.post',
                    ]
                ],
                'AssertUserIdMatches' => [
                    'permissions' => [
                        'edit.user',
                        'delete.user',
                        'find.unpublished.posts',
                        'fetch.files.rest',
                        'find.clients.rest',
                        'find.client.rest'
                    ]
                ],
                'MustBeOwnerAssertion' => [
                    'permissions' => [
                        'fetch.file.rest',
                        'delete.file.rest',
                    ]
                ],
                'AssertClientIdMatches' => [
                    'permissions' => [
                        //'fetch.file.rest',

                    ]
                ],
            ],
        ],
        'guards' => [
            'RouteGuard' => [
                'manage*' => 'admin',
                //'editGreeting' => 'admin',
            ]
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmRbac.log',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmRbac' => __DIR__ . '/../view',
        ],
    ],
];

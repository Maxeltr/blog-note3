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

use Zend\ServiceManager\Factory\InvokableFactory;

use Zend\Router\Http\Literal;

return [
    'controllers' => [
        'factories' => [
            Controller\AuthorizeController::class => Factory\Controller\AuthorizeControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            //'MustBeAuthorAssertion' => Assertion\MustBeAuthorAssertion::class
        ],
        'factories' => [
            Service\AuthorizationService::class => Factory\Service\AuthorizationServiceFactory::class,
            Assertion\AssertionPluginManager::class => Factory\Assertion\AssertionPluginManagerFactory::class,
            Logger::class => Factory\Logger\LoggerFactory::class,
        ],
        'invokables' => [
            //Assertion\MustBeAuthorAssertion::class => Assertion\MustBeAuthorAssertion::class,
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
    'rbac_module' => [
        'rbac_config' => [
            'roles' => [
                'admin' => [
                    'parent' => '',
                    'no_assertion' => true,
                    'permissions' => [

                    ]
                ],
                'moderator' => [
                    'parent' => 'admin',
                    //'no_assertion' => true,
                    'permissions' => [
                        'add.category',
                        'edit.category',
                        'delete.category',
                        'add.tag',
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
                    ]
                ],
            ],
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

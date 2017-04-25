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
    'rbac_module' => [
        'rbac_config' => [
            'roles' => [
                [
                    'name' => 'admin',
                    'parent' => '',
                    'permissions' => [

                    ]
                ],
                [
                    'name' => 'moderator',
                    'parent' => 'admin',
                    'permissions' => [

                    ]
                ],
                [
                    'name' => 'author',
                    'parent' => 'moderator',
                    'permissions' => [
                        'add.article',
                        'edit.article',
                        'delete.article',
                        'upload.image',
                        'delete.image',
                        'add.category',

                    ],
                ],
                [
                    'name' => 'anonymous',
                    'parent' => 'author',
                    'permissions' => []
                ],
            ],
            'assertions' => [       //TODO проверить все модули как себя ведут если удалить настройки
                [
                    'name' => 'MustBeAuthorAssertion',
                    'permissions' => [
                        'edit.article',
                        'delete.article',
                    ]
                ],

            ],
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmRbac.log',
        ],
    ],

];

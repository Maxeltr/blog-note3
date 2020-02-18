<?php

/*
 * The MIT License
 *
 * Copyright 2019 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmGame;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Literal;

return [
    'mxm_game' => [
        'listController' => [
            'ItemCountPerPage' => 9,
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmGame.log',
        ],
    ],
    'defaults' => [

    ],
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Controller\ListControllerFactory::class,
            Controller\WriteController::class => Controller\WriteControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\GameServiceInterface::class => Service\GameService::class,
            Mapper\MapperInterface::class => Mapper\ZendTableGatewayMapper::class,
        ],
        'factories' => [
            Service\GameService::class => Service\GameServiceFactory::class,
            Logger::class => Logger\LoggerFactory::class,
            Mapper\ZendTableGatewayMapper::class => Mapper\ZendTableGatewayMapperFactory::class,
            Hydrator\GameMapperHydrator::class => Hydrator\GameMapperHydratorFactory::class,

        ],
        'invokables' => [

        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\GameForm::class => Form\GameFormFactory::class,
        ]
    ],
    'router' => [
        'routes' => [
            'listGames' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/games[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listGames'
                    ],
                ],
            ],
            'detailGame' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/detail/game/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'detailGame'
                    ],
                ],
            ],
            'loadTextures' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/load/textures[/:id]',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'loadTextures'
                    ],
                ],
            ],
            'editGame' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/edit/game/:id',
                    'constraints' => [
                        'id' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'editGame'
                    ],
                ],
            ],
            'addGame' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/add/game',
                    'defaults' => [
                        'controller' => Controller\WriteController::class,
                        'action' => 'addGame'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmGame' => __DIR__ . '/../view',
        ],
    ],
];
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

namespace MxmFile;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Literal;

return [
    'mxm_file' => [
        'listController' => [
            'ItemCountPerPage' => 9,
        ],
        'logger' => [
            'path' => __DIR__ . '/../../../data/logs/MxmFile.log',
        ],
        'allowedFolders' => [
            'logs' => __DIR__ . '/../../../data/logs/',
        ],
    ],
    'defaults' => [

    ],
    'controllers' => [
        'factories' => [
            Controller\ListController::class => Controller\ListControllerFactory::class,
            Controller\DownloadController::class => Controller\DownloadControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            Service\FileServiceInterface::class => Service\FileService::class,
            Service\DownloadServiceInterface::class => Service\DownloadService::class,
            Mapper\MapperInterface::class => Mapper\ZendTableGatewayMapper::class,
        ],
        'factories' => [
            Mapper\ZendTableGatewayMapper::class => Mapper\ZendTableGatewayMapperFactory::class,
            Mapper\DirectoryMapper::class => Mapper\DirectoryMapperFactory::class,
            Service\FileService::class => Service\FileServiceFactory::class,
            Service\DownloadService::class => Service\DownloadServiceFactory::class,
            Logger::class => Logger\LoggerFactory::class,
            Hydrator\FileMapperHydrator\FileMapperHydrator::class => Hydrator\FileMapperHydrator\FileMapperHydratorFactory::class,
            Hydrator\Strategy\DateTimeFormatterStrategy::class => Hydrator\Strategy\DateTimeFormatterStrategyFactory::class,
            Hydrator\Strategy\OwnerStrategy::class => Hydrator\Strategy\OwnerStrategyFactory::class,
            Hydrator\Strategy\ClientStrategy::class => Hydrator\Strategy\ClientStrategyFactory::class,
        ],
        'invokables' => [

        ],
    ],
    'router' => [
        'routes' => [
            'listFiles' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/list/files[/:page]',
                    'constraints' => [
                        'page' => '[1-9]\d*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\ListController::class,
                        'action' => 'listFiles'
                    ],
                ],
            ],
            'downloadFileFromDir' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/download/file/:dir/:filename',
                    'constraints' => [
                        'filename' => '[a-zA-Z0-9._-]*',
                        'dir' => '[a-zA-Z0-9._-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\DownloadController::class,
                        'action' => 'downloadFileFromDir'
                    ],
                ],
            ],
            'downloadFileById' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/download/file/:id',
                    'constraints' => [
                        'id' => '[a-zA-Z0-9._-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\DownloadController::class,
                        'action' => 'downloadFileById'
                    ],
                ],
            ],

        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmFile' => __DIR__ . '/../view',
        ],
    ],
];
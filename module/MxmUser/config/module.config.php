<?php
namespace MxmUser;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => InvokableFactory::class,
        ],
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
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'MxmUser' => __DIR__ . '/../view',
        ],
    ],
];

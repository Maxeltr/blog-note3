<?php

namespace MxmDateTime;

return [
    'view_helpers' => [
        'aliases' => [
            'formatDateI18n' => View\Helper\FormatDateI18n::class,
        ],
        'factories' => [
            View\Helper\FormatDateI18n::class => View\Helper\FormatDateI18nFactory::class,
        ],
        'invokables' => [

        ]
    ],
    'service_manager' => [
        'aliases' => [
            'datetime' => DateTime::class,
            //DateTimeService::class => DateTimeServiceFactory::class,
        ],
        'factories' => [
            DateTime::class => Factory\DateTimeFactory::class,
            Service\DateTimeService::class => Service\DateTimeServiceFactory::class,
        ],
        'invokables' => [

        ],
    ],
    'defaults' => [
        'locale' => 'ru',
        'timezone' => 'UTC',
        'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
];

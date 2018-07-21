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
        ],
        'factories' => [
            DateTime::class => Factory\DateTimeFactory::class,
        ],
        'invokables' => [

        ],
    ],
    'defaults' => [
        'locale' => 'ru',
        'timezone' => 'Europe/Moscow',
        'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
];

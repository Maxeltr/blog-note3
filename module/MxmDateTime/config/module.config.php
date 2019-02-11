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
            'dateTime' => DateTime::class,
            'DateTime' => DateTime::class,
            'DateTimeImmutableFormatterStrategy' => Strategy\DateTimeImmutableFormatterStrategy::class,

        ],
        'factories' => [
            DateTime::class => Factory\DateTimeFactory::class,
            Service\DateTimeService::class => Service\DateTimeServiceFactory::class,
            Strategy\DateTimeImmutableFormatterStrategy::class => Strategy\DateTimeImmutableFormatterStrategyFactory::class,
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

<?php

namespace MxmHelpers;

return [
    'view_helpers' => [
        'aliases' => [
            'formatDateI18n' => View\Helper\FormatDateI18n::class,
        ],
        'factories' => [
            View\Helper\FormatDateI18n::class => View\Helper\FormatDateI18nFactory::class,
        ],
        'invokables' => [
            //'translate' => \Zend\I18n\View\Helper\Translate::class
        ]
    ],
    'defaults' => [
        'locale' => 'ru',
        'timezone' => 'Europe/Moscow',
        'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
];

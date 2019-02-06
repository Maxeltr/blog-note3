<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Navigation\Service\DefaultNavigationFactory;

return [
    'defaults' => [
        'locale' => 'ru',
        'timezone' => 'UTC',
        'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
    'db' => [
        'driver' => 'Pdo',
        'username' => 'root',
        'password' => '',
        'dsn' => 'mysql:dbname=blog-note;host=localhost',
        'driver_options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ]
    ],
    'soflomo_purifier' => [
        'config' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'pre,br,b,i,ol,ul,li,img[src|height|width|alt|class],blockquote,code,a[href]',
            //'CSS.MaxImgLength' => '20px',
            'HTML.MaxImgLength' => '500',
            //'HTML.Trusted' => 'true',
            //'HTML.SafeEmbed' => 'true',
            'AutoFormat.RemoveEmpty.RemoveNbsp' => 'true',
            'AutoFormat.RemoveEmpty' => 'true',     // удаляет пустые теги
            'Core.EscapeInvalidTags' => 'true',
            //'AutoFormat.AutoParagraph' => 'true',   // авто добавление <p> в тексте при переносе
            //'Output.TidyFormat' => true,
        ],
    ],
    'translator' => [
        'locale' => 'ru',
        'translation_file_patterns' => [
            [
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../../data/languages',
                'pattern'  => '%s.php',
            ],
        ],
    ],
    'navigation' => [
        'changeLanguageMenu' => [
            [
                'label' => 'Рус',
                'route' => 'changeLanguage',
                'action' => 'changeLanguage',
                'controller' => 'WriteController',
                'query' => ['lang' => 'Ru']
            ],
            [
                'label' => 'En',
                'route' => 'changeLanguage',
                'action' => 'changeLanguage',
                'controller' => 'WriteController',
                'query' => ['lang' => 'En']
            ],
        ],

    ],
    'service_manager' => [
        'abstract_factories' => [
            \Zend\Navigation\Service\NavigationAbstractServiceFactory::class,
        ],
//        'factories' => [
//            'navigation' => DefaultNavigationFactory::class,
//        ],
    ],
    'view_helper_config' => [
        'flashmessenger' => [
            'message_open_format' => '<div%s><button type="button" class="close"
            data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
            'message_close_string' => '</li></ul></div>',
            'message_separator_string' => '</li><li>',
        ],
    ],
];

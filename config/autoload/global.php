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

return [
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
    'blog_module' => [
        'listController' => [
            'ItemCountPerPage' => 10,
        ],
        'dateTime' => [
            'timezone' => 'Europe/Moscow',  //зона по умолчанию для создания дефолтных DateTime
            'locale' => 'ru_RU',
            'dateTimeFormat' => 'Y-m-d H:i:s',
        ]
        
    ],
];

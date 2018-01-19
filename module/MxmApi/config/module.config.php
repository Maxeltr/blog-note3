<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmApi;

return [
    'defaults' => [
        'locale' => 'ru',
        'timezone' => 'Europe/Moscow',
        'dateTimeFormat' => 'Y-m-d H:i:s',
    ],
    'service_manager' => [
        'factories' => [
            \MxmApi\V1\Rest\Post\PostResource::class => \MxmApi\V1\Rest\Post\PostResourceFactory::class,
            \MxmApi\V1\Rest\File\FileResource::class => \MxmApi\V1\Rest\File\FileResourceFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            \MxmApi\V1\Rest\Post\UserHydrator::class => \MxmApi\V1\Rest\Post\UserHydratorFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'mxm-api.rest.post' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/post[/:post_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Post\\Controller',
                    ],
                ],
            ],
            'mxm-api.rest.file' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/file[/:file_id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\File\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'mxm-api.rest.post',
            1 => 'mxm-api.rest.file',
        ],
    ],
    'zf-rest' => [
        'MxmApi\\V1\\Rest\\Post\\Controller' => [
            'listener' => \MxmApi\V1\Rest\Post\PostResource::class,
            'route_name' => 'mxm-api.rest.post',
            'route_identifier_name' => 'post_id',
            'collection_name' => 'post',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmBlog\Model\Post::class,
            'collection_class' => \MxmApi\V1\Rest\Post\PostCollection::class,
            'service_name' => 'post',
        ],
        'MxmApi\\V1\\Rest\\File\\Controller' => [
            'listener' => \MxmApi\V1\Rest\File\FileResource::class,
            'route_name' => 'mxm-api.rest.file',
            'route_identifier_name' => 'file_id',
            'collection_name' => 'file',
            'entity_http_methods' => [
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ],
            'collection_http_methods' => [
                0 => 'GET',
                1 => 'POST',
            ],
            'collection_query_whitelist' => [],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \MxmApi\V1\Rest\File\FileEntity::class,
            'collection_class' => \MxmApi\V1\Rest\File\FileCollection::class,
            'service_name' => 'file',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => 'HalJson',
            'MxmApi\\V1\\Rest\\File\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\File\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'MxmApi\\V1\\Rest\\Post\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
            ],
            'MxmApi\\V1\\Rest\\File\\Controller' => [
                0 => 'application/vnd.mxm-api.v1+json',
                1 => 'application/json',
                2 => 'multipart/form-data',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            \MxmBlog\Model\Post::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',
                'route_identifier_name' => 'post_id',
                'hydrator' => \Zend\Hydrator\ClassMethods::class,
            ],
            \MxmUser\Model\User::class => [
                //'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',    //TODo change to user
                //'route_identifier_name' => 'user_id',
                'hydrator' =>  \MxmApi\V1\Rest\Post\UserHydrator::class,
            ],
            \MxmBlog\Model\Category::class => [
                //'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',    //TODo change to user
                //'route_identifier_name' => 'user_id',
                'hydrator' => \Zend\Hydrator\ClassMethods::class,
            ],
            \Zend\Tag\ItemList::class => [
                //'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',    //TODo change to user
                //'route_identifier_name' => 'user_id',
                //'hydrator' => \Zend\Hydrator\ClassMethods::class,
                'is_collection' => true,
            ],
            \MxmBlog\Model\Tag::class => [
                //'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',    //TODo change to user
                //'route_identifier_name' => 'user_id',
                'hydrator' => \Zend\Hydrator\ClassMethods::class,
            ],
            \MxmApi\V1\Rest\Post\PostCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.post',
                'route_identifier_name' => 'post_id',
                'is_collection' => true,
            ],
            \MxmApi\V1\Rest\File\FileEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.file',
                'route_identifier_name' => 'file_id',
                'hydrator' => \Zend\Hydrator\ClassMethods::class,
            ],
            \MxmApi\V1\Rest\File\FileCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'mxm-api.rest.file',
                'route_identifier_name' => 'file_id',
                'is_collection' => true,
            ],
        ],
    ],
    'zf-content-validation' => [
        'MxmApi\\V1\\Rest\\File\\Controller' => [
            'input_filter' => 'MxmApi\\V1\\Rest\\File\\Validator',
        ],
    ],
    'input_filter_specs' => [
        'MxmApi\\V1\\Rest\\File\\Validator' => [
            0 => [
                'required' => false,
                'validators' => [
                    0 => [
                        'name' => \Zend\Validator\File\MimeType::class,
                        'options' => [
                            'mimeType' => [
                                'text/plain'
                            ],
                        ],
                    ],
                ],
                'filters' => [
                    0 => [
                        'name' => \Zend\Filter\File\RenameUpload::class,
                        'options' => [
                            'randomize' => true,
                            'target' => 'data/files/file.txt',
                        ],
                    ],
                ],
                'name' => 'file',
                'description' => 'file upload',
                'type' => \Zend\InputFilter\FileInput::class,
                'error_message' => 'file upload fail',
                'field_type' => 'multipart/form-data',
            ],
            1 => [
                'required' => false,
                'filters' => [],
                'validators' => [],
                'allow_empty' => false,
                'continue_if_empty' => false,
                'name' => 'filename',
            ],
            2 => [
                'required' => false,
                'filters' => [],
                'validators' => [],
                'allow_empty' => false,
                'continue_if_empty' => false,
                'name' => 'description',
            ],

        ],
    ],
];

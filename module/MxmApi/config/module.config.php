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
    'service_manager' => [
        'aliases' => [
            //Mapper::class => ArrayMapper::class,
        ],
        'factories' => [
            //ArrayMapper::class        => ArrayMapperFactory::class,
            V1\Rest\Post\Mapper::class => V1\Rest\Post\MapperFactory::class,

        ],
    ],
    'router' => [
        'routes' => [
            'mxm-api.rest.post' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/api/post[/:id]',
                    'defaults' => [
                        'controller' => 'MxmApi\\V1\\Rest\\Post\\Controller',
                    ],
                ],
            ],
        ],
    ],
];

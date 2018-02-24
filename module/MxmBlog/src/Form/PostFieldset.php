<?php

/*
 * The MIT License
 *
 * Copyright 2016 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

//Filename: /module/Blog/src/Blog/Form/PostFieldset.php
namespace MxmBlog\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use MxmBlog\Model\PostInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class PostFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;

    public function __construct(
        PostInterface $post,
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        $name = "post",
        $options = []
    ){
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($post);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;

        $this->add([
            'type' => 'hidden',
            'name' => 'id'
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'title',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Title')
            ]
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'rows' => '20',
            ],
            'options' => [
                'label' => $this->translator->translate('Text')
            ]
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'summary',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
                'rows' => '5',
            ],
            'options' => [
                'label' => $this->translator->translate('Summary')
            ]
        ]);

        $this->add([
            'type' => 'checkbox',
            'name' => 'isPublished',
            'attributes' => [
                //'class' => 'form-control',
            ],
            'options' => [
                'label' => $this->translator->translate('Publish'),
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
        ]);
    }

    public function init() {
        //parent::init();
        $this->add([
            'name' => 'category',
            'type' => 'MxmBlog\Form\CategoriesFieldset',
            'options' => [
                'use_as_base_fieldset' => true
            ]
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'tags',
            'options' => [
                //'label' => $this->translator->translate('Choose tags'),
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => [
                    'type' => 'MxmBlog\Form\TagsFieldset',
                ],
            ],
            'attributes' => [
                'class' => 'form-group',
            ],
        ]);

    }

    /**
     * Should return an array specification compatible with
     * {@link ZendInputFilterFactory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'id' => [
                'filters' => [
                    [
                        'name' => 'Int'
                    ],
                ],
            ],
            'title' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StripNewlines'
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'text' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'htmlpurifier'
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250000,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'summary' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'StripNewlines'
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'isPublished' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => 'Int'
                    ],
                ],
            ],
        ];
    }
}
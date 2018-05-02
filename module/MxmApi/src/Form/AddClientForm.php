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

namespace MxmApi\Form;

use Zend\Config\Config;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Zend\Hydrator\Reflection as ReflectionHydrator;
use MxmApi\Model\ClientInterface;
use Zend\Hydrator\HydratorInterface;

class AddClientForm extends Form implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;
    protected $grantTypes;

    public function __construct(
        InputFilter $inputFilter,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        Config $grantTypes,
        ClientInterface $client,
        HydratorInterface $hydrator,
        $name = "add_client",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($client);

        $this->setAttribute('method', 'post')
            ->setInputFilter($inputFilter);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;
        $this->grantTypes = $grantTypes->toArray();

        $this->add([
            'type' => 'text',
            'name' => 'client_id',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Application name')
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'client_secret',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Application secret key')
            ]
        ]);

        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'grant_types',
            'attributes' => [
                'type' => 'select',
                'required' => 'required',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => $this->translator->translate('Grant types'),
                'value_options' => $this->grantTypes,
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'scope',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Scope')
            ]
        ]);

//        $this->add([
//            'type' => 'csrf',
//            'name' => 'addClient_csrf',
//            'options' => [
//                'csrf_options' => [
//                'timeout' => 600
//                ]
//            ],
//        ]);

        $this->add([
            'type'  => 'hidden',
            'name' => 'redirect'
        ]);

        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => $this->translator->translate('Send'),
                'class' => 'btn btn-default'
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'client_id' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 80,
                            'translator' => $this->validatorTranslator
                        ]
                    ],
                ]
            ],
            'client_secret' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 80,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'grant_types' => [
                'filters' => [
                    ['name' => 'Int'],
                ],
            ],
            'scope' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 2000,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'redirect' => [
                'required' => false,
                'filters'  => [
                    ['name'=>'StringTrim']
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 0,
                            'max' => 2048
                        ]
                    ],
                ],
            ],
        ];
    }
}
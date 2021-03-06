<?php

/*
 * The MIT License
 *
 * Copyright 2017 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

namespace MxmUser\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterProviderInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

class SetPasswordForm extends Form implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;

    public function __construct(
        InputFilter $inputFilter,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        $name = "set_password",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post')
            ->setInputFilter($inputFilter);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;

        $this->add(array(
            'type' => 'hidden',
            'name' => 'token'
        ));

        $this->add([
            'type' => 'password',
            'name' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('New password')
            ]
        ]);

        $this->add([
            'type' => 'password',
            'name' => 'confirmPassword',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Confirm password')
            ]
        ]);

        $this->add([
            'type' => 'Laminas\Form\Element\Captcha',
            'name' => 'setPassword_captcha',
            'options' => [
                'label' => $this->translator->translate('Please verify you are human'),
                'captcha' => new \Laminas\Captcha\Figlet(),
            ],
        ]);

        $this->add([
            'type' => 'csrf',
            'name' => 'setPassword_csrf',
            'options' => [
                'csrf_options' => [
                'timeout' => 600
                ]
            ],
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
            'password' => [
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
                            'max' => 35,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'confirmPassword' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                            'translator' => $this->validatorTranslator
                        ]
                    ],
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 35,
                            'translator' => $this->validatorTranslator
                        ]
                    ],
                ]
            ],
            'token' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                    ['name' => 'StripNewlines'],
                ],
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'encoding'=>'UTF-8',
                            'min'=>1,
                            'max'=>250,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
        ];
    }
}
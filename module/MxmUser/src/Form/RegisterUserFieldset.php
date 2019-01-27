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

namespace MxmUser\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use MxmUser\Model\UserInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Zend\Config\Config;

class RegisterUserFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;
    protected $config;

    public function __construct(
        UserInterface $user,
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        Config $config,
        $name = "register_user",
        $options = array()
    ){
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($user);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;
        $this->config = $config;

        $this->add([
            'type' => 'text',
            'name' => 'username',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Username')
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'email',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Email')
            ]
        ]);

        $this->add([
            'type' => 'password',
            'name' => 'password',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => $this->translator->translate('Password')
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
            'type' => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => [
                'label' => $this->translator->translate('Please verify you are human'),
                //'captcha' => new \Zend\Captcha\Figlet(),
                'captcha' => [
                    'class' => 'Image',
                    'imgDir' => 'public/img/captcha',
                    'suffix' => '.png',
                    'imgUrl' => '/img/captcha/',
                    'imgAlt' => 'CAPTCHA Image',
                    //'font'   => './data/font/thorne_shaded.ttf',
                    'font'   => './public/css/fonts/Fixedsys500c.ttf',
                    'fsize'  => 24,
                    'width'  => 200,
                    'height' => 50,
                    'expiration' => 600,
                    'dotNoiseLevel' => 20,
                    'lineNoiseLevel' => 2
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
            ],
        ]);

        $this->add([
            'name' => 'localeId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'type' => 'select',
                'required' => 'required',
                'class' => 'form-control',
            ],
            'options' => [
                'label' => $this->translator->translate('Locale'),
                'value_options' => $this->config->mxm_user->locales->toArray(),
            ],
        ]);
    }

    public function init() {
        $this->add([
            'name' => 'timebelt',
            'type' => TimebeltFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true
            ]
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
            'username' => [
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
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'email' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StringToLower'],
                ],
                'validators' => [
                    [
                        'name' => 'EmailAddress',
                        'options' => [
                            'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                            'useMxCheck' => false,
                            'translator' => $this->validatorTranslator
                        ],
                    ],
                ]
            ],
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
            'localeId' => [
                'filters' => [
                    ['name' => 'Int'],
                ],
            ],
        ];
    }
}
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
use MxmRbac\Service\AuthorizationService;
use Zend\Config\Config;

class EditUserFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;
    protected $authorizationService;
    protected $roles;
    protected $config;

    public function __construct(
        UserInterface $user,
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        AuthorizationService $authorizationService,
        Config $roles,
        Config $config,
        $name = "edit_user",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($user);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;

        $this->authorizationService = $authorizationService;
        $this->roles = $roles;
        $this->config = $config;

//        $this->add([
//            'type' => 'hidden',
//            'name' => 'id'
//        ]);

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

        if ($this->authorizationService->isGranted('change.roles')) {
            $this->add([
                'type' => 'Zend\Form\Element\Select',
                'name' => 'role',
                'attributes' => [
                    'type' => 'select',
                    'required' => 'required',
                    'class' => 'form-control',
                ],
                'options' => [
                    'label' => $this->translator->translate('Roles'),
                    'value_options' => $this->roles->toArray(),
                ],
            ]);
        }

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
        //parent::init();
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
        $filters = [
//            'id' => [
//                'filters' => [
//                    ['name' => 'Int'],
//                ],
//            ],
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
                            'encoding'=>'UTF-8',
                            'min'=>1,
                            'max'=>250,
                            'translator' => $this->validatorTranslator
                        ]
                    ]
                ]
            ],
            'localeId' => [
                'filters' => [
                    ['name' => 'Int'],
                ],
            ],
        ];

        if ($this->authorizationService->isGranted('change.roles')) {
            $filters['role'] = [
                'filters' => [
                    ['name' => 'Int'],
                ],
            ];
        }

        return $filters;
    }
}
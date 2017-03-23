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
 
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Hydrator\HydratorInterface;

class ChangePasswordForm extends Form
{
    public function __construct(
        HydratorInterface $hydrator,
        InputFilter $inputFilter,
        $name = "change_password",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post')
            ->setHydrator($hydrator)
            ->setInputFilter($inputFilter);
        
        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
        ));
        
        $this->add([
            'type' => 'password',
            'name' => 'oldPassword',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Old password'
            ]
        ]);
        
        $this->add([
            'type' => 'password',
            'name' => 'newPassword',
            'attributes' => [
                'class' => 'form-control',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'New password'
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
                'label' => 'Confirm password'
            ]
        ]);
        
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Send'
            ]
        ]);
    }
    
    public function init() {
        //parent::init();
//        $this->add([
//            'name' => 'changePassword',
//            'type' => ChangePasswordFieldset::class,
//            'options' => [
//                'use_as_base_fieldset' => true
//            ]
//        ]);
        
//        $this->add([
//            'type' => 'submit',
//            'name' => 'submit',
//            'attributes' => [
//                'value' => 'Send'
//            ]
//        ]);
    }
    
    public function getInputFilterSpecification()
    {
        return [
            'id' => [
                'filters' => [
                    ['name' => 'Int'],
                ],
            ],
            'oldPassword' => [
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
                            'max' => 250,
                        ]
                    ]
                ]
            ],
            'newPassword' => [
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
                            'max' => 250,
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
                        'name' => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 250,
                        ]
                    ],
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'newPassword',                            
                        ],
                    ],
                ]
            ],
            
        ];
    }
    
}
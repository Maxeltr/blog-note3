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

class CredentialsFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(UserInterface $user, HydratorInterface $hydrator, $name = "user", $options = array())
    {
        parent::__construct($name, $options);
        
        $this->setHydrator($hydrator);
        $this->setObject($user);

        $this->add(array(
            'type' => 'text',
            'name' => 'email',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Email'
            )
        ));

        $this->add(array(
            'type' => 'password',
            'name' => 'password',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Password'
            )
        ));
        
        $this->add(array(
            'type' => 'password',
            'name' => 'confirmPassword',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Confirm Password'
            )
        ));
    }
    
    public function init() {
        
    }
    
    /**
     * Should return an array specification compatible with
     * {@link ZendInputFilterFactory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'email' => array(
                'required' => true,
                'filters'=>array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StripNewlines'
                    ),
                ),
                'validators' => array(
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'min'=>1,
                            'max'=>250,
                        )
                    )
                )
            ),
            'password' => array(
                'required' => true,
                'filters'=>array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StripNewlines'
                    ),
                ),
                'validators' => array(
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'min'=>1,
                            'max'=>250,
                        )
                    )
                )
            ),
            'confirmPassword' => array(
                'required' => true,
                'filters'=>array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'StripNewlines'
                    ),
                ),
                'validators' => [
                    ['name' => 'Identical',
                     'options' => [
                        'token' => 'password',                            
                    ]],
                    ['name' => 'StringLength',
                     'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 250,
                    ]],
                ]
            ),
            
        );
    }
}
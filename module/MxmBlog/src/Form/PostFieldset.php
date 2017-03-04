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

class PostFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(PostInterface $post, HydratorInterface $hydrator, $name = "post", $options = array())
    {
        parent::__construct($name, $options);
        
        $this->setHydrator($hydrator);
        $this->setObject($post);

        $this->add(array(
            'type' => 'hidden',
            'name' => 'id'
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'title',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Blog Title'
            )
        ));
        
        $this->add(array(
            'type' => 'textarea',
            'name' => 'text',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
                'rows' => '3',
            ),
            'options' => array(
                'label' => 'The text'
            )
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'summary',
            'attributes'=>array(
                'class' => 'form-control',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Blog Summary'
            )
        ));
        
        $this->add(array(
            'type' => 'checkbox',
            'name'=>'isPublished',
            'attributes'=>array(
                'class' => 'form-control',
            ),
            'options'=>array(
                'label'=>'Опубликовать',
                'checked_value' => 1,
                'unchecked_value' => 0,
            ),
        ));
    }
    
    public function init() {
        //parent::init();
        $this->add(array(
            'name' => 'category',
            'type' => 'MxmBlog\Form\CategoriesFieldset',
            'options' => array(
                'use_as_base_fieldset' => true
            )
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'tags',
            'options' => array(
                'label' => 'Please choose tags',
                'count' => 1,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => array(
                    'type' => 'MxmBlog\Form\TagFieldset',
                ),
            ),
        ));
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
            'id' => array(
                'filters'=>array(
                    array(
                        'name' => 'Int'
                    ),
                ),
            ),
            'title' => array(
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
            'text' => array(
                'required' => true,
                'filters'=>array(
                    array(
                        'name' => 'htmlpurifier'
                        //'name' => 'Soflomo\Purifier\Factory\PurifierFilterFactory'
                    ),
                ),
                'validators' => array(
                    array(
                        'name'=>'StringLength',
                        'options'=>array(
                            'encoding'=>'UTF-8',
                            'min'=>1,
                            'max'=>250000,
                        )
                    )
                )
            ),
            'summary' => array(
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
            'isPublished' => array(
                'required' => true,
                'filters'=>array(
                    array(
                        'name' => 'Int'
                    ),
                ),
            ),
        );
    }
}
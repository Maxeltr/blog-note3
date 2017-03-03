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

namespace MxmBlog\Form;

use MxmBlog\Model\CategoryInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use MxmBlog\Mapper\MapperInterface;

class CategoriesFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(
        CategoryInterface $category,
        MapperInterface $mapper,
        HydratorInterface $hydrator,
        $name = "categories",
        $options = array()
    ) {
        parent::__construct($name, $options);
        
        $categories = array();
        $paginator=$mapper->findAllCategories();
        $paginator->setItemCountPerPage(-1);    //получить все категории не разделенные на страницы
        foreach($paginator as $categoryObject) {
            $categories[$categoryObject->getId()] = $categoryObject->getTitle();
        }

        $this->setHydrator($hydrator);
        $this->setObject($category);
        
        $this->add(array(
            'name'=>'id',
            'type' => 'Zend\Form\Element\Select',
            'attributes'=>array(
                'type'=>'select',
                'required' => 'required',
                'class' => 'form-control',
            ),
            'options'=>array(
                'label'=>'Категория',
                //'disable_inarray_validator' => true,
                'value_options' => $categories,
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
        );
    }
}
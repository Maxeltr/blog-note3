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

use \DateTimeZone;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Form\Fieldset;
use MxmDateTime\Service\DateTimeService;
use Laminas\InputFilter\InputFilterProviderInterface;
use Zend\i18n\Translator\TranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Laminas\Validator\Timezone;

class TimebeltFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $translator;
    protected $validatorTranslator;
    protected $dateTimeService;

    public function __construct(
        DateTimeZone $timezone,
        DateTimeService $dateTimeService,
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        ValidatorTranslatorInterface $validatorTranslator,
        $name = "timebelt",
        $options = array()
    ) {
        parent::__construct($name, $options);

        $this->setHydrator($hydrator);
        $this->setObject($timezone);

        $this->translator = $translator;
        $this->validatorTranslator = $validatorTranslator;
        $this->dateTimeService = $dateTimeService;

        $this->add([
            'name' => 'timezone',
            'type' => 'Laminas\Form\Element\Select',
            'attributes' => [
                'type' => 'select',
                'required' => 'required',
                'class' => 'form-control',
                'value'	=> $this->dateTimeService->getDefaultTimezone()
            ],
            'options' => [
                'label' => $this->translator->translate('Timezone'),
                'value_options' => $this->dateTimeService->getTimezoneListWithGmtOffsets(),
            ],
        ]);

    }

    /**
     * Should return an array specification compatible with
     * {@link LaminasInputFilterFactory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'timezone' => [
                'filters' => [
                    //['name' => 'Int'],
                ],
                'validators' => [
                    [
                        'name' => 'Timezone',
                        'options' => [
                            //'type' => Timezone::LOCATION	//'location',
                            'translator' => $this->validatorTranslator
                        ]
                    ],
                ]
            ],
        ];
    }
}
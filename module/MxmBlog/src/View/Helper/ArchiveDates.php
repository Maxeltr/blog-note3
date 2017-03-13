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

namespace MxmBlog\View\Helper;

use MxmBlog\Mapper\MapperInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Validator\Date;
use Zend\Config\Config;

class ArchiveDates extends AbstractHelper
{
    protected $mapper;
    
    protected $dateValidator;
    
    protected $formatter;
    
    public function __construct(MapperInterface $mapper, Date $dateValidator, \IntlDateFormatter $formatter)
    {
        $this->mapper = $mapper;
        $this->dateValidator = $dateValidator;
        $this->formatter = $formatter;
    }

    public function __invoke()
    {
        $resultSet = $this->mapper->findPublishDates('month', 12);
        
        $archive = array();
        foreach ($resultSet as $key => $result) {
            if (array_key_exists('year', $result) && array_key_exists('month', $result) && 
                array_key_exists('total', $result)) {
                $this->dateValidator->setFormat('Y');
                if (!$this->dateValidator->isValid($result['year'])) {
                    break;
                }
                $this->dateValidator->setFormat('m');
                if (!$this->dateValidator->isValid($result['month'])) {
                    break;
                }
                if (!$result['year']) {
                    break;
                }
                
                $this->formatter->setPattern('Y');
                $year = $this->formatter->format(
                    \DateTime::createFromFormat('Y|', $result['year'])
                );
                
                $this->formatter->setPattern('LLLL');
                $month = $this->formatter->format(
                    \DateTime::createFromFormat('m|', $result['month'])
                );
                
                $archive[$year][$key]['monthNum'] = $result['month'];
                $archive[$year][$key]['monthName'] = $month;
                $archive[$year][$key]['total'] = $result['total'];
            }
        }
        
        return $archive;
    }
}
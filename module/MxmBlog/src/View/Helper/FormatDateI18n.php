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

use Zend\I18n\View\Helper\DateFormat;
use Zend\Config\Config;
use MxmBlog\Service\DateTimeInterface;
use Zend\Authentication\AuthenticationService;
use MxmUser\Model\UserInterface;

class FormatDateI18n extends DateFormat
{
    protected $datetime;

    protected $config;

    public function __construct(Config $config, \DateTimeInterface $datetime, AuthenticationService $authenticationService)
    {
        $this->config = $config;
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        parent::__construct();
    }

    public function __invoke(
       // \DateTimeInterface $datetime,
        $datetime,
        $dateType = \IntlDateFormatter::LONG,
        $timeType = \IntlDateFormatter::MEDIUM,
        $locale = null,
        $pattern = null
    ) {
        $timezone = null;
        $location = null;
        $user = $this->authenticationService->getIdentity();    //TODO gjcvjnhtnm rfr d ltktufnjh
        if ($user instanceof UserInterface) {
            $timezone = $user->getTimebelt();
            $location = $user->getLocale();
        }

        if ($timezone instanceof \DateTimeZone) {
            parent::setTimezone($timezone->getName());
        } else{
            parent::setTimezone($this->config->defaults->timezone);
        }

        if (!empty($location)) {
            parent::setLocale($location);
        } else{
            parent::setLocale($this->config->defaults->locale);
        }

        $date = $this->datetime->modify($datetime->format($this->config->defaults->dateTimeFormat));

        return parent::__invoke(
            $date,
            $dateType,
            $timeType,
            $locale,
            $pattern
        );
    }
}

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

namespace MxmMail\Service;

use Zend\Log\LoggerInterface;
use Zend\Mail\Message as MailMessage;
use Zend\Mime\Message as MimeMessage;
use Zend\Mail\Transport\Sendmail as SendMailTransport;
use Zend\Mime\Part as MimePart;

class MailService
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sendEmail($subject, $body, $from, $senderName, $to, $recipientName)
    {
        $mimePart = new MimePart($body);
        $mimePart->type = 'text/html';

        $mimeMess = new MimeMessage();
        $mimeMess->setParts(array($mimePart));

        $message = new MailMessage();
        $message->setEncoding('UTF-8');
        $message->setBody($mimeMess);
        $message->setSubject($subject);
        $message->addFrom($from, $senderName);
        $message->setTo($to, $recipientName());

        $transport = new SendMailTransport();
        $transport->send($message);
        $this->logger->info('Message was sent to ' . $to . ' with subject ' . $subject . '.');

        return $this;
    }
}
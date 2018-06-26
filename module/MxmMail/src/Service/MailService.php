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
use Zend\Mail\Address\AddressInterface;
use Zend\Mail\AddressList;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mail\Transport\TransportInterface;

class MailService
{
    /*
     * @var Zend\Log\LoggerInterface
     */
    protected $logger;

    /*
     * @var Zend\Mime\Part
     */
    protected $mimePart;

    /*
     * @var Zend\Mime\Message
     */
    protected $body;

    /*
     * @var Zend\Mail\Message
     */
    protected $mailMessage;

    /*
     * @var string
     */
    protected $encoding = 'UTF-8';

    /*
     * @var string
     */
    protected $subject;

    /*
     * @var Zend\Mail\AddressList
     */
    protected $from;

    /*
     * @var Zend\Mail\AddressList
     */
    protected $to;

    /*
     * Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator, LoggerInterface $logger, TransportInterface $transport)
    {
        $this->logger = $logger;

        $this->body = new MimeMessage();
        $this->mailMessage = new MailMessage();
        $this->from = new AddressList();
        $this->to = new AddressList();
        $this->transport = $transport;
        $this->translator = $translator;
    }

//    public function sendEmail($subject, $body, $from, $senderName, $to, $recipientName)
//    {
//        $mimePart = new MimePart($body);
//        $mimePart->type = 'text/html';
//
//        $mimeMess = new MimeMessage();
//        $mimeMess->setParts(array($mimePart));
//
//        $message = new MailMessage();
//        $message->setEncoding('UTF-8');
//        $message->setBody($mimeMess);
//        $message->setSubject($subject);
//        $message->addFrom($from, $senderName);
//        $message->setTo($to, $recipientName);
//
//        $transport = new SendMailTransport();
//        $transport->send($message);
//        $this->logger->info('Message was sent to ' . $to . ' with subject ' . $subject . '.');
//
//        return $this;
//    }

    public function send()
    {
        $this->mailMessage->setEncoding($this->encoding);
        $this->mailMessage->setBody($this->body);
        $this->mailMessage->setSubject($this->subject);
        $this->mailMessage->setFrom($this->from);
        $this->mailMessage->setTo($this->to);

        $this->transport->send($this->mailMessage);

        $adressesTo = '';
        $adresses = $this->mailMessage->getTo();
        $adresses->rewind();
        while($adresses->valid()) {
            $adressesTo .= $adresses->current()->toString();
            $adresses->next();
        }
        $this->logger->info($this->translator->translate('Message was sent to') . ' ' . $adressesTo . ' '
            . $this->translator->translate('with subject') . ' "' . $this->subject . '".'
        );

        return $this;
    }

    public function setBody($message, $type = null)
    {
        $mimePart = new MimePart($message);
        if ($type !== null) {
            $mimePart->setType($type);
        } else {
            $mimePart->setType('text/plain');
        }

        $this->body->setParts([$mimePart]);

        return $this;
    }

    public function addBody($message, $type = null)
    {
        $mimePart = new MimePart($message);
        if ($type !== null) {
            $mimePart->setType($type);
        }

        $this->body->addPart([$mimePart]);

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setTo($emailOrAddress, $name = null)
    {
        $this->to = new AddressList();
        $this->to->add($emailOrAddress, $name);

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setFrom($emailOrAddress, $name = null)
    {
        $this->from = new AddressList();
        $this->from->add($emailOrAddress, $name);

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }
}
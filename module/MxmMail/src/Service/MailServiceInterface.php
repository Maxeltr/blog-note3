<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <maxim.eltratov@yandex.ru>.
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

use Laminas\Mail\Message;
use Laminas\Mail\Address\AddressInterface;
use Laminas\Mail\AddressList;

interface MailServiceInterface
{
    /**
     * Send a mail message.
     *
     * @return
     *
     */
    public function send();

    /**
     * Set (overwrite) address to the To recipients
     *
     * string|Address\AddressInterface $emailOrAddress
     * @param  null|string $name
     *
     * @return $this
     *
     */
    public function setTo($emailOrAddress, $name = null);

    /**
     * Access the address list of the To header
     *
     * @return AddressList
     */
    public function getTo();

    /**
     * Set (overwrite) a "From" address
     *
     * string|Address\AddressInterface $emailOrAddress
     * @param  string|null $name
     *
     * @return $this
     *
     */
    public function setFrom($emailOrAddress, $name = null);

    /**
     * Retrieve list of From senders
     *
     * @return AddressList
     */
    public function getFrom();

    /**
     * Set the message body
     *
     * @param mixed String or Stream containing the content $message
     * @param  string|null $type
     *
     * @return $this
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setBody($message, $type = null);

    /**
    * Add a new part to the current message
    *
    * @param mixed String or Stream containing the content $message
    * @param  string|null $type
    *
    * @return $this
    *
    * @throws Exception\InvalidArgumentException
    */
    public function addBody($message, $type = null);

    /**
     * Return the currently set message body
     *
     * @return Laminas\Mime\Message object
     */
    public function getBody();

    /**
     * Set encoding
     *
     * @param string $encoding
     *
     * @return $this
     *
     */
    public function setEncoding($encoding);

    /**
     * Get the message encoding
     *
     * @return string
     */
    public function getEncoding();

    /**
     * Set the message subject header value
     *
     * @param  string $subject
     *
     * @return $this
     *
     */
    public function setSubject($subject);

    /**
     * Get the message subject header value
     *
     * @return null|string
     */
    public function getSubject();
}

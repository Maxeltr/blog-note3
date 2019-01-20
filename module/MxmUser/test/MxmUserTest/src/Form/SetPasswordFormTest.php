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

namespace MxmUserTest\Form;

use MxmUser\Form\SetPasswordForm;
use Zend\InputFilter\InputFilter;

class SetPasswordFormTest extends \PHPUnit\Framework\TestCase
{
    private $form;
    private $data;

    public function setUp()
    {
        $this->data = array(
            'token' => '',
            'password' => ''
        );

        $this->form = new SetPasswordForm(new InputFilter);
        $csrf = $this->form->get('setPassword_csrf')->getValue();
        $this->data['setPassword_csrf'] = $csrf;

//        $captcha = $this->form->get('setPassword_captcha')->getCaptcha()->getWord();
//        $this->data['setPassword_captcha'] = $captcha;

        parent::setUp();
    }

//    public function testEmptyValues()  //TODO
//    {
//        $form = $this->form;
//        $data = $this->data;
//
//        $this->assertFalse($form->setData($data)->isValid());
//
//	$data['token'] = 'token';
//	$this->assertFalse($form->setData($data)->isValid());
//
//        $data['password'] = 'password';
//        $this->assertTrue($form->setData($data)->isValid());
//    }
//
//    public function testPasswordElement()  //TODO
//    {
//        $form = $this->form;
//        $data = $this->data;
//
//        $data['token'] = 'token';
//
//        $data36 = '123456789012345678901234567890123456';
//        $data['password'] = $data36;
//	$this->assertFalse($form->setData($data)->isValid());
//
//        $data35 = '12345678901234567890123456789012345';
//        $data['password'] = $data35;
//        $this->assertTrue($form->setData($data)->isValid());
//    }
//
//    public function testTokenElement()  //TODO
//    {
//
//    }

    public function testStringTrim()
    {
        $form = $this->form;
        $data = $this->data;

	$data['token'] = ' token ';
	$data['password'] = ' password ';

	$form->setData($data)->isValid();
        $validatedData = $form->getData();

	$this->assertSame('token', $validatedData['token']);
	$this->assertSame('password', $validatedData['password']);
    }
}
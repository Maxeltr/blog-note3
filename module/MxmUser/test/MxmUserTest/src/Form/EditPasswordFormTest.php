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

use MxmUser\Form\EditPasswordForm;
use Zend\InputFilter\InputFilter;

class EditPasswordFormTest extends \PHPUnit_Framework_TestCase
{
    private $form;
    private $data;
    private $data36;
    private $data35;

    public function setUp()
    {
        $this->data = array(
            'id' => '',
            'oldPassword' => '',
            'newPassword' => '',
            'confirmPassword' => ''
        );
        $this->data36 = '123456789012345678901234567890123456';
        $this->data35 = '12345678901234567890123456789012345';

        $this->form = new EditPasswordForm(new InputFilter());

        parent::setUp();
    }

    public function testEmptyValues()
    {
        $form = $this->form;
        $data = $this->data;

        $this->assertFalse($form->setData($data)->isValid());

        $data['id'] = 1;
        $this->assertFalse($form->setData($data)->isValid());

        $data['oldPassword'] = 'oldPasswordTest';
        $this->assertFalse($form->setData($data)->isValid());

        $data['newPassword'] = 'newPasswordTest';
        $this->assertFalse($form->setData($data)->isValid());

        $data['confirmPassword'] = 'newPasswordTest';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testOldPasswordElement()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['newPassword'] = 'newPasswordTest';
        $data['confirmPassword'] = 'newPasswordTest';

        $data['oldPassword'] = '';
        $this->assertFalse($form->setData($data)->isValid());

        $data['oldPassword'] = $this->data36;
        $this->assertFalse($form->setData($data)->isValid());

        $data['oldPassword'] = $this->data35;
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testNewPasswordAndConfirmPasswordElements()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['oldPassword'] = 'newPasswordTest';

        $data['newPassword'] = '';
        $data['confirmPassword'] = '';
        $this->assertFalse($form->setData($data)->isValid());

        $data['newPassword'] = 'newPassword';
        $data['confirmPassword'] = 'confirmPassword';
        $this->assertFalse($form->setData($data)->isValid());

        $data['newPassword'] = $this->data36;
        $data['confirmPassword'] = $this->data36;
        $this->assertFalse($form->setData($data)->isValid());

        $data['newPassword'] = $this->data35;
        $data['confirmPassword'] = $this->data35;
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testStringTrim()
    {
        $form = $this->form;
        $data = $this->data;
        $data['id'] = 1;
        $data['oldPassword'] = ' oldPassword ';
        $data['newPassword'] = ' password ';
        $data['confirmPassword'] = ' password ';

        $form->setData($data)->isValid();
        $validatedData = $form->getData();

        $this->assertSame('oldPassword', $validatedData['oldPassword']);
        $this->assertSame('password', $validatedData['newPassword']);
        $this->assertSame('password', $validatedData['confirmPassword']);
    }
}
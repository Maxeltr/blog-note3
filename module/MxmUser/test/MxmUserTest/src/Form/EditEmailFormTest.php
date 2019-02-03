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

use MxmUser\Form\EditEmailForm;
use Zend\InputFilter\InputFilter;
use Zend\i18n\Translator\TranslatorInterface;
use Zend\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use Zend\InputFilter\Input;
use MxmUser\Validator\IdenticalStrings;
use Zend\ServiceManager\ServiceManager;

class EditEmailFormTest extends \PHPUnit\Framework\TestCase
{
    private $form;
    private $data;
    private $translator;
    protected $validatorTranslator;

    public function setUp()
    {
        $this->data = array(
            //'id' => '',
            'newEmail' => '',
            'confirmEmail' => '',
            'password' => '',
            'editEmail_csrf' => ''
        );

        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->validatorTranslator = $this->prophesize(ValidatorTranslatorInterface::class);

        $this->form = new EditEmailForm(new InputFilter(), $this->translator->reveal(), $this->validatorTranslator->reveal());
        $csrf = $this->form->get('editEmail_csrf')->getValue();
        $this->data['editEmail_csrf'] = $csrf;

        parent::setUp();
    }

    public function testEmptyValues()
    {
        $form = $this->form;
        $data = $this->data;

        $this->assertFalse($form->setData($data)->isValid());

        $data['newEmail'] = 'email@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

	$data['confirmEmail'] = 'email@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

        $data['password'] = 'password';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testNewEmailElement()
    {
        $form = $this->form;
        $data = $this->data;
        //$data['id'] = 1;
        $data['confirmEmail'] = 'email@testmail.ru';
	$data['password'] = 'password';

        $data['newEmail'] = "test";
        $this->assertFalse($form->setData($data)->isValid());

        $data['newEmail'] = 'email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testConfirmEmailElement()
    {
        $form = $this->form;
        $data = $this->data;
        //$data['id'] = 1;
        $data['newEmail'] = 'email@testmail.ru';
	$data['password'] = 'password';

        $data['confirmEmail'] = "test";
        $this->assertFalse($form->setData($data)->isValid());

        $data['confirmEmail'] = 'email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

    public function testIdenticalEmails()
    {
        $form = $this->form;
        $data = $this->data;
        //$data['id'] = 1;
	$data['password'] = 'password';

        $data['confirmEmail'] = 'email@testmail.ru';
	$data['newEmail'] = 'newEmail@testmail.ru';
        $this->assertFalse($form->setData($data)->isValid());

	$data['newEmail'] = 'email@testmail.ru';
        $data['confirmEmail'] = 'Email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());

	$data['newEmail'] = 'Email@testmail.ru';
        $data['confirmEmail'] = 'email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());

	$data['newEmail'] = 'email@testmail.ru';
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testPasswordElement()
    {
        $form = $this->form;
        $data = $this->data;
        //$data['id'] = 1;
        $data['newEmail'] = 'email@testmail.ru';
	$data['confirmEmail'] = 'email@testmail.ru';


        //$data251 = '12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901';
        $data36 = '123456789012345678901234567890123456';
        $data['password'] = $data36;
	$this->assertFalse($form->setData($data)->isValid());

        //$data250 = '1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
        $data35 = '12345678901234567890123456789012345';
        $data['password'] = $data35;
        $this->assertTrue($form->setData($data)->isValid());
    }

	public function testStringTrim()
    {
        $form = $this->form;
        $data = $this->data;
        //$data['id'] = 1;
	$data['newEmail'] = ' email@testmail.ru ';
        $data['confirmEmail'] = ' email@testmail.ru ';
	$data['password'] = ' password ';

	$form->setData($data)->isValid();
        $validatedData = $form->getData();

	$this->assertSame('email@testmail.ru', $validatedData['newEmail']);
	$this->assertSame('email@testmail.ru', $validatedData['confirmEmail']);
	$this->assertSame('password', $validatedData['password']);
    }
}
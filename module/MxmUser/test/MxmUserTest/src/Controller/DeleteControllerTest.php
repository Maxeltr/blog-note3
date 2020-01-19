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

namespace MxmUserTest\Controller;

use MxmUser\Controller\DeleteController;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\ServiceManager\ServiceManager;
use MxmUser\Service\UserServiceInterface;
use MxmUser\Service\UserService;
use MxmUser\Model\User;

class DeleteControllerTest extends AbstractHttpControllerTestCase //\PHPUnit_Framework_TestCase
{
    protected $userService;
    protected $serviceLocator;

    protected $traceError = true;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../../../config/application.config.php',
            $configOverrides
        ));

	$this->userService = $this->prophesize(UserServiceInterface::class);

        $user = new User();
        $this->userService->findUserById("1")->willReturn($user);
	$this->userService->deleteUser($user)->willReturn(true);

        $this->serviceLocator = $this->getApplicationServiceLocator();
	$this->serviceLocator->setAllowOverride(true);

        $this->serviceLocator->setService('config', $this->updateConfig($this->serviceLocator->get('config')));
        $this->serviceLocator->setService(UserServiceInterface::class, $this->userService->reveal());

        $this->serviceLocator->setAllowOverride(false);

        parent::setUp();
    }

	/**
     * @covers MxmUser\Controller\DeleteController::deleteUserAction
     *
     */
    public function testDeleteUserAction()
    {
        $this->dispatch('/delete/user/1');
        //$this->assertResponseStatusCode(200);
        $this->assertModuleName('MxmUser');
        $this->assertControllerName(DeleteController::class);
        $this->assertControllerClass('DeleteController');
        $this->assertMatchedRouteName('deleteUser');
    }

    /**
     * @covers MxmUser\Controller\DeleteController::deleteUserAction
     *
     */
//    public function testDeleteUserActionAfterConfirmationDeleteting()
//    {
//	$postData = [
//            'delete_confirmation' => 'yes',
//	];
//        $this->dispatch('/delete/user/1', 'POST', $postData);
//        //$this->assertResponseStatusCode(302);
//	//$this->assertRedirectTo('/listUsers');
//        //$this->assertModuleName('MxmUser');
//        //$this->assertControllerName(ListController::class);
//        //$this->assertControllerClass('ListController');
//    }

    protected function updateConfig($config)
    {
        //$config['db'] = [];
        return $config;
    }
}
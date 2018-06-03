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

namespace MxmBlog;

use Zend\Mvc\MvcEvent;
use MxmBlog\Logger;
use MxmBlog\Service\PostServiceInterface;
use MxmUser\Service\UserServiceInterface;
use MxmBlog\Exception\NotAuthorizedBlogException;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use MxmBlog\Mapper\MapperInterface;
use Zend\Http\PhpEnvironment\Request;

class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(EventInterface $event)
    {
        $application = $event->getTarget();
        $serviceManager = $application->getServiceManager();
        $eventManager = $application->getEventManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError']);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onError']);

        $postMapper = $serviceManager->get(MapperInterface::class);
        $usEventManager = $serviceManager->get(UserServiceInterface::class)->getEventManager();
        $usEventManager->attach('deleteUser', function (EventInterface $event) use ($postMapper) {
            $posts = $postMapper->findPostsByUser($event->getParam('user'), false)->setItemCountPerPage(-1);
            $postMapper->deletePosts($posts);
        });
    }

    public function onError(MvcEvent $event)
    {
        $message = '';

        $request = new Request();
            $uri = $request->getServer('REQUEST_URI', null);
        if (isset($uri)) {
            $message = "Request URI: " . $uri . "\n";
        }

        $message .= "Controller: " . $event->getController() . "\n";
        $message .= "Error message: " . $event->getError() . "\n";

        $ex = $event->getParam('exception');
        if ($ex !== null) {
            $message .= "Exception: " . get_class($ex) . "\n";
            $message .= "Message: " . $ex->getMessage() . "\n";
            $message .= "File: " . $ex->getFile() . "\n";
            $message .= "Line: " . $ex->getLine() . "\n";
            $message .= "Stack trace:\n " . $ex->getTraceAsString() . "\n";
        } else {
            $message .= "No exception available.\n";
        }

        $logger = $event->getApplication()->getServiceManager()->get(Logger::class);
        $logger->err($message);

        if ($ex instanceof NotAuthorizedBlogException) {

            return $event->getTarget()->redirect()->toRoute('notAuthorized');
        }
    }
}

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

namespace MxmRbac\Guard;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventManagerInterface;
use MxmRbac\Exception\NotAuthorizedException;
use MxmRbac\Service\AuthorizationServiceInterface;
use MxmRbac\Exception\InvalidArgumentException;
use Zend\Config\Config;

class RouteGuard implements RouteGuardInterface
{
    /**
     * Event priority
     */
    const EVENT_PRIORITY = -5;

    /**
     * Constant for guard that can be added to the MVC event result
     */
    const GUARD_UNAUTHORIZED = 'guard-unauthorized';

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    use ListenerAggregateTrait;

    public function __construct(AuthorizationServiceInterface $authorizationService, Config $config)
    {
        $this->authorizationService = $authorizationService;
        $this->config = $config;

        if (! isset($this->config->rbac_module->guards->RouteGuard)) {
            throw new InvalidArgumentException('No RouteGuard options in module config');
        }
    }

    public function attach(EventManagerInterface $events, $priority = self::EVENT_PRIORITY)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError'], $priority);
    }

    public function onRoute(MvcEvent $event)
    {
        if ($this->isGranted($event)) {
            return;
        }

        $event->setError(self::GUARD_UNAUTHORIZED);
        $event->setParam('exception', new NotAuthorizedException(
            'You are not authorized to access this resource',
            403
        ));
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);

        $application  = $event->getApplication();
        $eventManager = $application->getEventManager();
        $eventManager->triggerEvent($event);

        $event->stopPropagation(true);
    }

    public function onError(MvcEvent $event)
    {
        if ($event->isError() && $event->getError() === self::GUARD_UNAUTHORIZED) {
            $url = $event->getRouter()->assemble(array(), array('name' => 'notAuthorized'));

            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(301);
            $response->sendHeaders();

            return $response;
        }
    }

    public function isGranted(MvcEvent $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();

        if ('notAuthorized' === $matchedRouteName) {    //in order to prevent "redirected you too many times"
            return true;
        }

        $exceptRole = null;
        $prohibitedRoutes = $this->config->rbac_module->guards->RouteGuard->toArray();  //get prohibited routes from config

        foreach (array_keys($prohibitedRoutes) as $route) {
            if (fnmatch($route, $matchedRouteName, FNM_CASEFOLD)) {     //is given route is prohibited?
                $exceptRole = $prohibitedRoutes[$route];                //get role-exception from config for given route
                break;
            }
        }

        if (null === $exceptRole || '*' === $exceptRole) {    // Allow route, if $exceptRole is empty, so prohibited routes are absent.
            return true;                                    // Allow route, if $exceptRole is '*', so given route is allow for all.
        }

        return $this->authorizationService->matchIdentityRoles($exceptRole);
    }
}
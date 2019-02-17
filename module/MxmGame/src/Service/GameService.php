<?php

/*
 * The MIT License
 *
 * Copyright 2019 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmGame\Service;

use Zend\Log\Logger;
use Zend\Config\Config;
use Zend\Paginator\Paginator;
use Zend\Authentication\AuthenticationService;
use MxmGame\Exception\InvalidArgumentException;
use MxmRbac\Service\AuthorizationService;
use Zend\Paginator\Adapter\ArrayAdapter;

class GameService implements GameServiceInterface
{
    /**
     * @var Zend\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var MxmRbac\Service\AthorizationService
     */
    protected $authorizationService;

    /**
     * @var Zend\Config\Config;
     */
    protected $config;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Config $config,
        Logger $logger
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->config = $config;
        $this->logger = $logger;
    }

	/**
     * {@inheritDoc}
     */
    public function findAllGames()
    {
        //$this->authenticationService->checkIdentity();

        //$this->authorizationService->checkPermission('find.all.games');


        $games = [];

        return new Paginator(new ArrayAdapter($games));
    }

    public function loadTextures($id)
    {
        $pathname = 'c:\js\p3d\textures.png';       //TODO refactor

        $file = file_get_contents($pathname);

        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename="' . $pathname . '"',
        ));
        $response->setContent($file);

        return $response;
    }
}
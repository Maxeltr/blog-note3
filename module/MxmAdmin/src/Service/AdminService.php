<?php

/*
 * The MIT License
 *
 * Copyright 2018 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
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

namespace MxmAdmin\Service;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\Config\Config;
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmAdmin\Exception\RuntimeException;
use Zend\Http\Response;
use MxmAdmin\Exception\InvalidArgumentException;
use Zend\Filter\StaticFilter;
use Zend\Stdlib\ErrorHandler;
use Zend\Log\Logger;
use MxmFile\Mapper\DirectoryMapper;

class AdminService implements AdminServiceInterface
{
    /**
     * @var DateTimeInterface
     */
    protected $datetime;

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
     * @var Zend\Http\Response
     */
    protected $response;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var MapperInterface
     */
    protected $mapper;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        Response $response,
        Config $config,
        Logger $logger,
        DirectoryMapper $mapper
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->response = $response;
        $this->config = $config;
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllLogs()
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('find.logs');

        $dir = $this->config->mxm_admin->logs->path;

        if (! is_dir($dir)) {
            throw new RuntimeException($dir . ' is not directory.');
        }

        return $this->mapper->findAllFiles($dir);
    }

    public function deleteLogs($files)
    {
        if (! is_string($files) && ! is_array($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string or array; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('delete.logs');

        $this->mapper->deleteFiles($files, $this->config->mxm_admin->logs->path);
    }
}
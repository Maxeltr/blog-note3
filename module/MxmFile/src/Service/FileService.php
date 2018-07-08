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

namespace MxmFile\Service;

use Zend\Log\Logger;
use Zend\Config\Config;
use Zend\Paginator\Paginator;
use Zend\Authentication\AuthenticationService;
use MxmFile\Exception\InvalidArgumentException;
use MxmRbac\Service\AuthorizationService;
use MxmFile\Mapper\MapperInterface;

class FileService implements FileServiceInterface
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
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var Zend\Log\Logger
     */
    protected $fileMapper;

    public function __construct(
        \DateTimeInterface $datetime,
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        MapperInterface $fileMapper,
        Config $config,
        Logger $logger
    ) {
        $this->datetime = $datetime;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->fileMapper = $fileMapper;
        $this->config = $config;
        $this->logger = $logger;
    }

	/**
     * {@inheritDoc}
     */
    public function findAllFiles()
    {
        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('find.all.files');

        return $this->fileMapper->findAllFiles();
    }

    public function downloadFile($fileId)
    {
        if (! is_string($fileId)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($fileId) ? get_class($fileId) : gettype($fileId))
            ));
        }

        $this->authenticationService->checkIdentity();

        $file = $this->fileMapper->findFileById($fileId);

        $this->authorizationService->checkPermission('download.file', $file);

        $path = $file->getPath();

        return $this->fileMapper->downloadFile($path);
    }

    public function deleteFile($fileId)
    {
        if (! is_string($fileId)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($fileId) ? get_class($fileId) : gettype($fileId))
            ));
        }

        $this->authenticationService->checkIdentity();

        $file = $this->fileMapper->findFileById($fileId);

        $this->authorizationService->checkPermission('delete.file', $file);

        return $this->fileMapper->deleteFile($file);
    }

    public function deleteFiles($files)
    {
        if (! is_string($files) && ! is_array($files) && ! ($files instanceof Paginator)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string or array or instance of Paginator; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('delete.files');

        return $this->fileMapper->deleteFiles($files);
    }
}
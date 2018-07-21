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
use Zend\Authentication\AuthenticationService;
use MxmRbac\Service\AuthorizationService;
use MxmFile\Mapper\MapperInterface;
use MxmFile\Mapper\DirectoryMapper;
use MxmFile\Exception\RuntimeException;
use MxmFile\Exception\InvalidArgumentException;
use Zend\Filter\StaticFilter;

class DownloadService implements DownloadServiceInterface
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

    /**
     * @var MxmFile\Mapper\MapperInterface
     */
    protected $fileMapper;

    /**
     * @var MxmFile\Mapper\DirectoryMapper
     */
    protected $dirMapper;

    public function __construct(
        AuthenticationService $authenticationService,
        AuthorizationService $authorizationService,
        MapperInterface $fileMapper,
        DirectoryMapper $dirMapper,
        Config $config,
        Logger $logger
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->fileMapper = $fileMapper;
        $this->dirMapper = $dirMapper;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function downloadFileById($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($id) ? get_class($id) : gettype($id))
            ));
        }

        $this->authenticationService->checkIdentity();

        $file = $this->fileMapper->findFileById($id);

        $this->authorizationService->checkPermission('download.file', $file);

        return $this->fileMapper->downloadFile($file);
    }

    public function downloadFileFromDir($name, $dir)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($name) ? get_class($name) : gettype($name))
            ));
        }
        $name = StaticFilter::execute($name, 'Zend\Filter\BaseName');

        if (! is_string($dir)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($dir) ? get_class($dir) : gettype($dir))
            ));
        }
        $dir = StaticFilter::execute($dir, 'Zend\Filter\BaseName');

        $this->authenticationService->checkIdentity();

        $this->authorizationService->checkPermission('download.file');

        $path = $this->config->mxm_file->allowedFolders->$dir;

        if (! $path) {
            throw new RuntimeException('Directory "' . $dir . '" is not allowed for downloading files.');
        }

        return $this->dirMapper->downloadFile($path . $name);
    }
}
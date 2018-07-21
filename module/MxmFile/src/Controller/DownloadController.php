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

namespace MxmFile\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Config\Config;
use Zend\Log\Logger;
use MxmFile\Service\DownloadServiceInterface;
use Zend\i18n\Translator\TranslatorInterface;

class DownloadController  extends AbstractActionController
{
    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    /**
     * @var \MxmFile\Service\DownloadServiceInterface
     */
    protected $downloadService;

    /**
     * @var Zend\i18n\Translator\TranslatorInterface
     */
    protected $translator;

    public function __construct(
        DownloadServiceInterface $downloadService,
        Config $config,
        Logger $logger,
        TranslatorInterface $translator
    ) {
        $this->downloadService = $downloadService;
        $this->config = $config;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function downloadFileFromDirAction()
    {
        $file = $this->params()->fromRoute('filename', '');
        $dir = $this->params()->fromRoute('dir', '');
        
        $response = $this->downloadService->downloadFileFromDir($file, $dir);

        return $response;
    }

    public function downloadFileByIdAction()
    {
        $id = $this->params()->fromRoute('id', '');
        $response = $this->downloadService->downloadFileById($id);

        return $response;
    }
}

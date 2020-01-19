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
namespace MxmFile\Mapper;

use Laminas\Config\Config;
use Laminas\Log\Logger;
use Laminas\Http\Response;
use Laminas\Filter\StaticFilter;
use Laminas\Paginator\Paginator;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use MxmFile\Model\File;
use MxmFile\Exception\RuntimeException;
use MxmFile\Exception\InvalidArgumentException;

class DirectoryMapper
{
    /**
     * @var DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Laminas\Config\Config;
     */
    protected $config;

    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    /**
     * @var \Laminas\Hydrator\HydratorInterface
     */
    protected $hydrator;

    /**
     * @var Laminas\Http\Response
     */
    protected $response;

    public function __construct(
        \DateTimeInterface $datetime,
        Config $config,
        Logger $logger,
        HydratorInterface $hydrator,
        Response $response
    ){
        $this->datetime = $datetime;
        $this->config = $config;
        $this->logger = $logger;
        $this->hydrator = $hydrator;
        $this->response = $response;
    }

    public function findAllFiles($dir)
    {
        if (! $dirHandle = opendir($dir)) {
            throw new RuntimeException('Can not open directory ' . $dir . '.');
        }

        $files = [];

        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $dir . $file;

            $fileData = [
                'filename' => $file,
                'path' => $filePath,
                'size' => filesize($filePath),
                //'changeDate' => $this->datetime->setTimestamp (filemtime($filePath))->format($this->config->defaults->dateTimeFormat),
                'changeDate' => date ($this->config->defaults->dateTimeFormat, filemtime($filePath)),
                'type' => filetype($filePath)
            ];

            $files[] = $this->hydrator->hydrate($fileData, new File());
        }

        closedir($dirHandle);

        return new Paginator(new ArrayAdapter($files));
    }

    public function deleteFiles($files, $dirPath)
    {
        if (! is_string($files) && ! is_array($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string or array; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        if (! is_string($dirPath)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be string; received "%s"',
                (is_object($dirPath) ? get_class($dirPath) : gettype($dirPath))
            ));
        }

        if (is_string($files)) {
            $files = explode(' ', $files);
        }

        foreach ($files as $file) {
            $path = $dirPath . StaticFilter::execute($file, 'Laminas\Filter\BaseName');

            if (!is_readable($path)) {
                throw new RuntimeException('Path "' . $path . '" is not readable.');
            }

            if (! is_file($path)) {
                throw new RuntimeException('File "' . $path . '" does not exist.');
            }

            $result = $this->unlinkFile($path);
            if (! $result) {
                $fp = fopen($path, "r+");
                if ($fp) {
                    $ft = ftruncate($fp, 0);
                    if (! $ft) {
                        $this->logger->err('File: ' . $path . ' was not truncated.');
                    }
                    fclose($fp);
                    $this->logger->err('File: ' . $path . ' was truncated.');
                } else {
                    $this->logger->err('File: ' . $path . ' Can not open.');
                }
            }
        }

        return;
    }

    public function downLoadFile($path)
    {
        if (!is_readable($path)) {
            throw new RuntimeException('Path "' . $path . '" is not readable.');
        }

        if (! is_file($path)) {
            throw new RuntimeException('File "' . $path . '" does not exist.');
        }

        $headers = $this->response->getHeaders();
        $headers->addHeaderLine("Content-type: application/octet-stream");
        $headers->addHeaderLine("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
        $headers->addHeaderLine("Content-length: " . filesize($path));
//        $headers->addHeaderLine("Cache-control: private"); //use this to open files directly

        $fileContent = file_get_contents($path);
        if ($fileContent !== false) {
            $this->response->setContent($fileContent);
        } else {
            throw new RuntimeException("Can't read file");
        }

        return $this->response;
    }

    private function unlinkFile($filePath)
    {
        ErrorHandler::start();
        $test = unlink($filePath);
        $error = ErrorHandler::stop();
        if (! $test) {
            $this->logger->err('Cannot remove file ' . $filePath . '. ' . $error . '.');
        }

        return $test;
    }
}
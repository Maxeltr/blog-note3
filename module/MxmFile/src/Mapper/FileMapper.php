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

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Rhumsaa\Uuid\Uuid;
use Zend\Config\Config;
use Zend\Http\Response;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Mapper\MapperInterface;
use Zend\Log\Logger;
use Zend\Stdlib\ErrorHandler;
use MxmFile\Exception\RecordNotFoundException;
use MxmFile\Exception\InvalidArgumentException;
use Zend\Stdlib\ArrayUtils;
use MxmFile\Model\FileInterface;

class FileMapper
{
    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $fileTableGateway;

    /**
     * @var DateTimeInterface
     */
    protected $datetime;

    /**
     * @var Zend\Config\Config
     */
    protected $config;

    /**
     * @var Zend\Http\Response
     */
    protected $response;

    /**
     * @var MxmRbac\Service\AuthorizationService
     */
    protected $authorizationService;

    /**
     * @var MxmUser\Mapper\MapperInterface
     */
    protected $userMapper;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(
        TableGateway $fileTableGateway,
        \DateTimeInterface $datetime,
        Config $config,
        Response $response,
        AuthorizationService $authorizationService,
        MapperInterface $mapper,
        Logger $logger
    ){
        $this->fileTableGateway = $fileTableGateway;
        $this->datetime = $datetime;
        $this->config = $config;
        $this->response = $response;
        $this->authorizationService = $authorizationService;
        $this->userMapper = $mapper;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllFiles()
    {
        $paginator = new Paginator(new DbTableGateway($this->fileTableGateway, null, ['upload_date' => 'DESC']));

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function findAllFilesByOwner(UserInterface $owner = null)
    {
        $paginator = new Paginator(new DbTableGateway($this->fileTableGateway, ['owner' => $owner->getId()], ['upload_date' => 'DESC']));

        return $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function findFileById($fileId)
    {
        $resultSet = $this->fileTableGateway->select(['file_id' => $fileId]);
        if (0 === count($resultSet)) {
            throw new RecordNotFoundException('File ' . $fileId . 'not found.');
        }

        return $resultSet->current();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteFile($file)
    {
        $result = $this->unlinkFile($file->getPath());
        if (! $result) {
            $this->logger->err("Cannot delete file. Id: " . $file->getFileId() . ".");

            return false;
        }

        $result = $this->fileTableGateway->delete(['file_id' => $file->getFileId()]);
        if (! $result) {
            $this->logger->err("Cannot delete file record. Id: " . $file->getFileId() . ".");
        }

            return $result;
	}

    /**
     * {@inheritDoc}
     */
    public function deleteFiles($files)
    {
        if ($files instanceof Paginator) {
            $files = ArrayUtils::iteratorToArray($files->setItemCountPerPage(-1));
        }

        if (! is_array($files) or empty($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        $filePaths = [];
        $func = function ($value) use (&$filePaths) {
            if (is_string($value)) {
                try {
                    $file = $this->findFileById($value);
                } catch (\Exception $ex) {
                    $this->logger->err("Cannot delete file. File record not found. Id: " . $value
                        . ". Message: " . $ex->getMessage()
                        . ". File: " . $ex->getFile()
                        . ". Line: " .$ex->getLine()
                        . "."
                    );

                    return;
                }
                $filePaths[] = $file->getPath();

                return $value;
            } elseif ($value instanceof FileInterface) {
                $filePaths[] = $value->getPath();

                return $value->getId();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of FileInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $fileIds = array_map($func, $files);

        foreach($filePaths as $filePath) {
            $this->unlinkFile($filePath);
        }

        $where = new Where();
        $where->in('file_id', $fileIds);

        return $this->fileTableGateway->delete($where);
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
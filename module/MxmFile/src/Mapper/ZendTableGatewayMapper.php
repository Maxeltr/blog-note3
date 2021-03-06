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

use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Db\TableGateway\TableGateway;
use Rhumsaa\Uuid\Uuid;
use Laminas\Config\Config;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Laminas\Stdlib\ErrorHandler;
use MxmFile\Exception\RecordNotFoundException;
use MxmFile\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArrayUtils;
use MxmFile\Model\FileInterface;
use MxmUser\Model\UserInterface;
use Laminas\Db\Sql\Where;
use MxmFile\Exception\DataBaseErrorException;
use MxmFile\Exception\RuntimeException;

class ZendTableGatewayMapper implements MapperInterface
{
    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $fileTableGateway;

    /**
     * @var Laminas\Config\Config
     */
    protected $config;

    /**
     * @var Laminas\Http\Response
     */
    protected $response;

    /**
     * @var Laminas\Log\Logger
     */
    protected $logger;

    public function __construct(
        TableGateway $fileTableGateway,
        Config $config,
        Response $response,
        Logger $logger
    ){
        $this->fileTableGateway = $fileTableGateway;
        $this->config = $config;
        $this->response = $response;
        $this->logger = $logger;
    }

    /*
     * {@inheritDoc}
     */
    public function insertFile($file)
    {
        $this->fileTableGateway->insert($file);
        $resultSet = $this->fileTableGateway->select(['file_id' => $file['file_id']]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
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

    public function downLoadFile(FileInterface $file)
    {
        $path = $file->getPath();

        if (! is_readable($path)) {
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

    /**
     * {@inheritDoc}
     */
    public function deleteFile(FileInterface $file)
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

        if (! is_array($files)) {
            throw new InvalidArgumentException(sprintf(
                'The data must be array or instanceof Paginator; received "%s"',
                (is_object($files) ? get_class($files) : gettype($files))
            ));
        }

        if (empty($files)) {
            return 0;
        }

        $filePathsAndIds = $this->getIdsAndPathsOfFiles($files);

        foreach($filePathsAndIds as $fileId => $filePath) {
            if (! $this->unlinkFile($filePath)) {
                unset($filePathsAndIds[$fileId]);
            }
        }

        $where = new Where();
        $where->in('file_id', array_keys($filePathsAndIds));

        return $this->fileTableGateway->delete($where);
    }

    private function getIdsAndPathsOfFiles($files)
    {
        $filePathsAndIds = [];
        $func = function ($value) use (&$filePathsAndIds) {
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
                $filePathsAndIds[$value] = $file->getPath();

                return;
            } elseif ($value instanceof FileInterface) {
                $filePathsAndIds[$value->getFileId()] = $value->getPath();

                return;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Invalid value in data array detected, value must be a string or instance of FileInterface, %s given.',
                    (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        array_map($func, $files);

        return $filePathsAndIds;
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
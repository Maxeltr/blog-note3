<?php
namespace MxmApi\V1\Rest\File;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use Zend\Db\TableGateway\TableGateway;
use DomainException;
use InvalidArgumentException;
use Traversable;
use Rhumsaa\Uuid\Uuid;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Stdlib\ArrayUtils;
use Zend\Config\Config;
use Zend\Http\Response;

class FileResource extends AbstractResourceListener
{
    /**
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * @var \DateTime
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

    public function __construct(TableGateway $tableGateway, \DateTimeInterface $datetime, Config $config, Response $response)
    {
        $this->tableGateway = $tableGateway;
        $this->datetime = $datetime;
        $this->config = $config;
        $this->response = $response;
    }
    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        $inputFilter = $this->getInputFilter();
        $file = $inputFilter->getValue('file');

        if (empty($file['name']) or empty($file['tmp_name'])) {
            return new ApiProblem(500, 'Create operation failed. No data received.');
        }

        $id = Uuid::uuid4()->toString();

        $this->tableGateway->insert([
            'id' => $id,
            'filename' => $file['name'],
            'path' => $file['tmp_name'],
            'description' => "",
            'uploaded' => $this->datetime->modify('now')->format($this->config->defaults->dateTimeFormat)
        ]);

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            return new ApiProblem(500, 'Insert operation failed or did not result in new row');
        }

        return $resultSet->current();
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        if (! Uuid::isValid($id)) {
            throw new DomainException('Invalid identifier provided', 404);
        }

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            throw new DomainException('File record not found in DB', 404);
        }
        $fileEntity = $resultSet->current();

        $path = $fileEntity->getPath();

        if (!is_readable($path)) {
            $this->response->setStatusCode(404);
            return;
        }

        unlink($path);

        $result = $this->tableGateway->delete(['id' => $id]);

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        if (! Uuid::isValid($id)) {
            return new ApiProblem(404, 'Invalid identifier provided');
        }

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            return new ApiProblem(404, 'File record not found in DB');
        }
        $fileEntity = $resultSet->current();

        $path = $fileEntity->getPath();

        if (!is_readable($path)) {
            return new ApiProblem(404, 'File not found');
        }

        $headers = $this->response->getHeaders();
        $headers->addHeaderLine("Content-type: application/octet-stream");
        $headers->addHeaderLine("Content-Disposition: attachment; filename=\"" . $fileEntity->getFilename() . "\"");
        $headers->addHeaderLine("Content-length: " . filesize($path));
        $headers->addHeaderLine("Cache-control: private"); //use this to open files directly

        $fileContent = file_get_contents($path);
        if ($fileContent !== false) {
            $this->response->setContent($fileContent);
        } else {
            return new ApiProblem(500, "Can't read file");
        }

        return $this->response;
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        return new FileCollection(new DbTableGateway($this->tableGateway, null, ['uploaded' => 'DESC']));
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}

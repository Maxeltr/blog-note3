<?php
namespace MxmApi\V1\Rest\File;

use Zend\Db\TableGateway\TableGateway;
use DomainException;
use InvalidArgumentException;
use Traversable;
use Rhumsaa\Uuid\Uuid;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Stdlib\ArrayUtils;
use Zend\Config\Config;
use Zend\Http\Response;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Mapper\MapperInterface;
use MxmUser\Exception\RecordNotFoundUserException;

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

    /**
     * @var MxmRbac\Service\AuthorizationService
     */
    protected $authorizationService;

    /**
     * @var MxmUser\Mapper\MapperInterface
     */
    protected $userMapper;

    public function __construct(
        TableGateway $tableGateway,
        \DateTimeInterface $datetime,
        Config $config,
        Response $response,
        AuthorizationService $authorizationService,
        MapperInterface $mapper
    ){
        $this->tableGateway = $tableGateway;
        $this->datetime = $datetime;
        $this->config = $config;
        $this->response = $response;
        $this->authorizationService = $authorizationService;
        $this->userMapper = $mapper;
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

        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticatedIdentity = $identity->getAuthenticationIdentity();
            if (! $authenticatedIdentity) {
                unlink($file['tmp_name']);

                return new ApiProblem(401, 'Unauthorized');
            }
        }

        try {
            $currentUser = $this->userMapper->findUserById($authenticatedIdentity['user_id']);
        } catch (RecordNotFoundUserException $e) {
            return new ApiProblem(401, 'Unauthorized. Identity not found.');
        }

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted('create.file.rest')) {
            unlink($file['tmp_name']);

            return new ApiProblem(403, 'Forbidden. Permission create.file.rest is required.');
        }

        $id = Uuid::uuid4()->toString();

        $this->tableGateway->insert([
            'id' => $id,
            'filename' => $file['name'],
            'path' => $file['tmp_name'],
            'description' => $inputFilter->getValue('description'),
            'uploaded' => $this->datetime->modify('now')->format($this->config->defaults->dateTimeFormat),
            'owner' => $authenticatedIdentity['user_id'],
            'client' => $authenticatedIdentity['client_id'],
        ]);

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            unlink($file['tmp_name']);

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
            return new ApiProblem(404, 'Invalid identifier provided');
        }

        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticatedIdentity = $identity->getAuthenticationIdentity();
            if (! $authenticatedIdentity) {
                return new ApiProblem(401, 'Unauthorized');
            }
        }

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            return new ApiProblem(404, 'File record not found.');
        }
        $fileEntity = $resultSet->current();

        try {
            $currentUser = $this->userMapper->findUserById($authenticatedIdentity['user_id']);
        } catch (RecordNotFoundUserException $e) {
            return new ApiProblem(401, 'Unauthorized. Identity not found.');
        }

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted('delete.file.rest', $fileEntity)) {
            return new ApiProblem(403, 'Forbidden. Permission delete.file.rest is required.');
        }

        $path = $fileEntity->getPath();

        if (!is_readable($path)) {
            return new ApiProblem(404, 'File not found.');
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
        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticatedIdentity = $identity->getAuthenticationIdentity();
            if (! $authenticatedIdentity) {
                return new ApiProblem(401, 'Unauthorized');
            }
        }

        $user = null;
        if (array_key_exists('user', $params)) {				//fetch by user
            $userId = (string) $params['user'];
            try {
                $user = $this->userMapper->findUserById($userId);
            } catch (RecordNotFoundUserException $e) {
                return new ApiProblem(404, 'User not found.');
            }
        }

        try {
            $currentUser = $this->userMapper->findUserById($authenticatedIdentity['user_id']);
        } catch (RecordNotFoundUserException $e) {
            return new ApiProblem(401, 'Unauthorized. Identity not found.');
        }

        $owner = $user !== null ? $user : $currentUser;

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted('delete.files.rest', $owner)) {
            return new ApiProblem(403, 'Forbidden. Permission delete.files.rest is required.');
        }

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
        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticatedIdentity = $identity->getAuthenticationIdentity();
            if (! $authenticatedIdentity) {
                return new ApiProblem(401, 'Unauthorized');
            }
        }

        try {
            $currentUser = $this->userMapper->findUserById($authenticatedIdentity['user_id']);
        } catch (RecordNotFoundUserException $e) {
            return new ApiProblem(401, 'Unauthorized. Identity not found.');
        }

        if (! Uuid::isValid($id)) {
            return new ApiProblem(404, 'Invalid identifier provided');
        }

        $resultSet = $this->tableGateway->select(['id' => $id]);
        if (0 === count($resultSet)) {
            return new ApiProblem(404, 'File record not found in DB');
        }
        $fileEntity = $resultSet->current();

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted('fetch.file.rest', $fileEntity)) {
            return new ApiProblem(403, 'Forbidden. Permission fetch.file.rest is required.');
        }

        $params = $this->getEvent()->getQueryParams();
	$download = array_key_exists('d', $params) ? $params['d'] : false;

        if ($download === '1') {
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

        return $fileEntity;
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = [])
    {
        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticatedIdentity = $identity->getAuthenticationIdentity();
            if (! $authenticatedIdentity) {
                return new ApiProblem(401, 'Unauthorized');
            }
        }

        $user = null;
        if (array_key_exists('user', $params)) {
            $userId = (string) $params['user'];
            try {
                $user = $this->userMapper->findUserById($userId);
            } catch (RecordNotFoundUserException $e) {
                return new ApiProblem(404, 'User not found.');
            }
        }

        try {
            $currentUser = $this->userMapper->findUserById($authenticatedIdentity['user_id']);
        } catch (RecordNotFoundUserException $e) {
            return new ApiProblem(401, 'Unauthorized. Identity not found.');
        }

        $owner = $user !== null ? $user : $currentUser;

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted('fetch.files.rest', $owner)) {
            return new ApiProblem(403, 'Forbidden. Permission fetch.files.rest is required.');
        }

        return new FileCollection(new DbTableGateway($this->tableGateway, ['owner' => $owner->getId()], ['uploaded' => 'DESC']));
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

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
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use MxmFile\Mapper\MapperInterface as FileMapperInterface;
use MxmUser\Exception\RecordNotFoundUserException;
use Zend\Log\Logger;
use Zend\Stdlib\ErrorHandler;
use Zend\Filter\StaticFilter;
use DateTimeImmutable;

class FileResource extends AbstractResourceListener
{
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

    /**
     * @var MxmFile\Mapper\MapperInterface
     */
    protected $fileMapper;

    /**
     * @var Zend\Log\Logger
     */
    protected $logger;

    public function __construct(
        DateTimeImmutable $datetime,
        Config $config,
        Response $response,
        AuthorizationService $authorizationService,
        UserMapperInterface $mapper,
        FileMapperInterface $fileMapper,
        Logger $logger
    ){
        $this->datetime = $datetime;
        $this->config = $config;
        $this->response = $response;
        $this->authorizationService = $authorizationService;
        $this->userMapper = $mapper;
        $this->fileMapper = $fileMapper;
        $this->logger = $logger;
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

        $filePathName = StaticFilter::execute($file['tmp_name'], 'Zend\Filter\RealPath');

        $identity = $this->getMvcAuthIdentity();
        if (! $identity) {
            $this->unlinkFile($filePathName);
            $this->logger->err(sprintf('Unauthenticated attempt to create file. Temp file was removed: %s.', $filePathName));

            return new ApiProblem(401, 'Unauthenticated.');
        }

        if (! $this->checkPermissionForIdentity($identity, 'create.file.rest')) {
            $this->unlinkFile($filePathName);
            $this->logger->err(sprintf('Temp file was removed: %s.', $filePathName));

            return new ApiProblem(403, 'Forbidden. Permission create.file.rest is required.');
        }

        $id = Uuid::uuid4()->toString();

        $this->fileMapper->insertFile([
            'file_id' => $id,
            'filename' => $file['name'],
            'path' => $filePathName,
            'description' => $inputFilter->getValue('description'),
            'upload_date' => $this->datetime->modify('now')->format($this->config->defaults->dateTimeFormat),
            'change_date' => $this->datetime->modify('now')->format($this->config->defaults->dateTimeFormat),
            'owner' => $identity['user_id'],
            'client' => $identity['client_id'],
            'size' => filesize($filePathName),
            'type' => filetype($filePathName),
        ]);

        $fileEntity = $this->findFileById($id);
        if (! $fileEntity) {
            $this->unlinkFile($filePathName);
            $this->logger->err(sprintf('Insert operation failed or did not result in new row. Temp file was removed: %s.', $filePathName));

            return new ApiProblem(401, 'Insert operation failed or did not result in new row.');
        }

        return $fileEntity;
    }

    /**
     * @param String $id
     *
     * @return false|\MxmFile\Model\File
     */
    private function findFileById($id)
    {
        try {
            $fileEntity = $this->fileMapper->findFileById(['file_id' => $id]);
        } catch (\Exception $e) {
            $this->logger->err(sprintf('Cannot find file with id: %s. Code: %s. File: %s. Line: %s. Message: %s.',
                $id,
                $e->getCode(),
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ));

            return false;
        }

        return $fileEntity;
    }

    /**
     * @param String $id
     *
     * @return false|\MxmUser\Model\User
     */
    private function findUserById($id)
    {
        try {
            $user = $this->userMapper->findUserById($id);
        } catch (\Exception $e) {
            $this->logger->err(sprintf('Cannot find user with id: %s. Code: %s. File: %s. Line: %s. Message: %s.',
                $id,
                $e->getCode(),
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ));

            return false;
        }

        return $user;
    }

    /**
     * @param Array $identity
     * @param String $permission
     * @param Object $content
     *
     * @return bool
     */
    private function checkPermissionForIdentity($identity, $permission, $content = null)
    {
        $currentUser = $this->findUserById($identity['user_id']);
        if (! $currentUser) {
            $this->logger->err(sprintf('Forbidden for %s. Cannot find user.', $identity['user_id']));

            return false;
        }

        $this->authorizationService->setIdentity($currentUser);
        if (! $this->authorizationService->isGranted($permission, $content)) {
            $this->logger->err(sprintf('Forbidden for %s. Permission %s is required.', $identity['user_id'], $permission));

            return false;
        }

        return true;
    }

    /**
     * @return false|Array
     */
    private function getMvcAuthIdentity()
    {
        $identity = $this->getIdentity();
        if ($identity instanceof AuthenticatedIdentity) {
            $authenticationIdentity = $identity->getAuthenticationIdentity();
            if ($authenticationIdentity) {

                return $authenticationIdentity;
            }
        }

        return false;
    }

    /**
     * @param String $filePath
     *
     * @return bool
     */
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

        $identity = $this->getMvcAuthIdentity();
        if (! $identity) {
            $this->logger->err(sprintf('Unauthenticated attempt to delete file: %s.', $id));

            return new ApiProblem(401, 'Unauthenticated.');
        }

        $fileEntity = $this->findFileById($id);
        if (! $fileEntity) {
            $this->logger->err(sprintf('Cannot delete file: %s. Record not found.', $id));

            return new ApiProblem(401, 'Cannot delete file. Record not found.');
        }

        if (! $this->checkPermissionForIdentity($identity, 'delete.file.rest', $fileEntity)) {

            return new ApiProblem(403, 'Forbidden. Permission delete.file.rest is required.');
        }

        if (! $this->fileMapper->deleteFile($fileEntity)) {

            return new ApiProblem(401, 'Cannot remove file.');
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
        } else {
            return new ApiProblem(401, 'Can not get identity');
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
        if (! Uuid::isValid($id)) {
            return new ApiProblem(404, 'Invalid identifier provided');
        }

        $identity = $this->getMvcAuthIdentity();
        if (! $identity) {
            $this->logger->err(sprintf('Unauthenticated attempt to fetch file: %s.', $id));

            return new ApiProblem(401, 'Unauthenticated.');
        }

        $fileEntity = $this->findFileById($id);
        if (! $fileEntity) {
            $this->logger->err(sprintf('Cannot fetch file: %s. Record not found.', $id));

            return new ApiProblem(401, 'Cannot fetch file. Record not found.');
        }

        if (! $this->checkPermissionForIdentity($identity, 'fetch.file.rest', $fileEntity)) {

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
        $identity = $this->getMvcAuthIdentity();
        if (! $identity) {
            $this->logger->err('Unauthenticated attempt to fetch all files.');

            return new ApiProblem(401, 'Unauthenticated.');
        }

        $currentUser = $this->findUserById($identity['user_id']);
        if (! $currentUser) {
            $this->logger->err(sprintf('Cannot fetch all files. Authenticated user %s not found.', $identity['user_id']));

            return new ApiProblem(404, 'Cannot fetch all files. Authenticated user not found.');
        }

        if (array_key_exists('user', $params)) {
            $userId = (string) $params['user'];
            $user = $this->findUserById($userId);
            if (! $user) {
                $this->logger->err(sprintf('Cannot fetch all files by user %s. User not found.', $userId));

                return new ApiProblem(404, 'Cannot fetch all files by user. User not found.');
            }

            if (! $this->checkPermissionForIdentity($identity, 'fetch.files.by.user.rest', $user)) {

                return new ApiProblem(403, 'Forbidden. Permission fetch.files.by.user.rest is required.');
            }

            return $this->fileMapper->findAllFilesByOwner($user);
        }

        if (! $this->checkPermissionForIdentity($identity, 'fetch.all.files.rest')) {

            return new ApiProblem(403, 'Forbidden. Permission fetch.all.files.rest is required.');
        }

        return $this->fileMapper->findAllFiles();
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

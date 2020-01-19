<?php
namespace MxmApi\V1\Rest\File;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Hydrator\ClassMethodsHydrator as ClassMethods;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Config\Config;
use Laminas\Http\Response;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use MxmApi\Logger;
use MxmFile\Mapper\MapperInterface as FileMapperInterface;

class FileResourceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = new Config($container->get('config'));

        $datetime = $container->get('datetime');

        $response = new Response();

        $authorizationService = $container->get(AuthorizationService::class);
        $mapper = $container->get(UserMapperInterface::class);
        $fileMapper = $container->get(FileMapperInterface::class);

        $logger = $container->get(Logger::class);

        return new FileResource($datetime, $config, $response, $authorizationService, $mapper, $fileMapper, $logger);
    }
}

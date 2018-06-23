<?php
namespace MxmApi\V1\Rest\File;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator\ClassMethods;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;
use Zend\Http\Response;
use MxmRbac\Service\AuthorizationService;
use MxmUser\Mapper\MapperInterface as UserMapperInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use MxmApi\Logger;
use MxmFile\Mapper\MapperInterface as FileMapperInterface;

class FileResourceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = new Config($container->get('config'));

        $timezone = new \DateTimeZone($config->defaults->timezone);
        $datetime = new \DateTimeImmutable('now', $timezone);

        $response = new Response();

        $authorizationService = $container->get(AuthorizationService::class);
        $mapper = $container->get(UserMapperInterface::class);
        $fileMapper = $container->get(FileMapperInterface::class);

        $logger = $container->get(Logger::class);

        return new FileResource($datetime, $config, $response, $authorizationService, $mapper, $fileMapper, $logger);
    }
}

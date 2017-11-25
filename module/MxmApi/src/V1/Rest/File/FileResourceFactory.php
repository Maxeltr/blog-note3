<?php
namespace MxmApi\V1\Rest\File;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Hydrator\ClassMethods;
use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Config;
use Zend\Http\Response;

class FileResourceFactory
{
    public function __invoke($services)
    {
        $config = new Config($services->get('config'));

        $timezone = new \DateTimeZone($config->defaults->timezone);
        $datetime = new \DateTimeImmutable('now', $timezone);

        $response = new Response();

        $table = 'files';
        $adapter = $services->get(Adapter::class);
        $resultSet = new HydratingResultSet(new ClassMethods(false), new FileEntity());
        $tableGateway = new TableGateway($table, $adapter, null, $resultSet);

        return new FileResource($tableGateway, $datetime, $config, $response);
    }
}

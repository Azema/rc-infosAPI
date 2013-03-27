<?php

namespace RcApi\Service;

use PhlyRestfully\Resource;
use RcApi\ClubDbPersistence;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClubDbPersistenceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $table = $services->get('RcApi\DbTable');
        return new ClubDbPersistence($table);
    }
}

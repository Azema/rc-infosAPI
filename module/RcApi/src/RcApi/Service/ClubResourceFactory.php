<?php

namespace RcApi\Service;

use PhlyRestfully\Resource;
use RcApi\ClubDbPersistence;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClubResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $events   = $services->get('EventManager');
        $resource = new Resource;
        $resource->setEventManager($events);

        $listener = $services->get('RcApi\PersistenceListener');
        $events->attach($listener);

        return $resource;
    }
}

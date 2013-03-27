<?php

namespace RcApi\Service;

use PhlyRestfully\ResourceController;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClubsResourceControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services   = $controllers->getServiceLocator();
        $resource   = $services->get('RcApi\ClubResource');
        $config     = $services->get('config');
        $config     = isset($config['rc_api']) ? $config['rc_api'] : array();
        $pageSize   = isset($config['page_size']) ? $config['page_size'] : 10;

        $controller = new ResourceController('RcApi\ClubsResourceController');
        $controller->setResource($resource);
        $controller->setPageSize($pageSize);
        $controller->setRoute('rc_clubs_api/public');
        $controller->setCollectionHttpOptions(array('GET','POST'));
        $controller->setCollectionName('clubs');
        return $controller;
    }
}

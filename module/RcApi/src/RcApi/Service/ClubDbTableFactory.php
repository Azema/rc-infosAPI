<?php

namespace RcApi\Service;

use RcApi\ClubDbTable;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClubDbTableFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        $adapter = $services->get('RcApi\DbAdapter');
        $config  = $services->get('Config');
        $table   = 'clubs';
        if (isset($config['rc_api'])
            && isset($config['rc_api']['clubs']['table'])
        ) {
            $table = $config['rc_api']['clubs']['table'];
        }

        return new ClubDbTable($adapter, $table);
    }
}

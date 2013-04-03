<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function _initAutoload()
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => dirname(__FILE__),
            'namespace' => '',
        ));
        $resourceLoader->addResourceType('services', 'services/', 'Service')
            ->addResourceType('model', 'models/', 'Model');
        return $resourceLoader;
    }
}


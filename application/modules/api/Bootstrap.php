<?php

class Api_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * Application resource namespace
     * @var false|string
     */
    protected $_appNamespace = 'Api';

    public function apiInitConfig()
    {
    	$config = include dirname(__FILE__) . '/configs/' . APPLICATION_ENV . '.php';
    	if (is_array($config)) {
    		$this->setOptions($config);
    	}
    	$this->bootstrap('db');
    }

    public function apiInitHelperHalLinks()
    {
    	$halLinks = new Rca_View_Helper_HalLinks();
    	$halLinks->setServerUrlHelper(new Zend_View_Helper_ServerUrl());
    	$halLinks->setUrlHelper(new Zend_View_Helper_Url());
    	Rca_View_JsonRenderer::$halLinks = $halLinks;
    }

    protected function _initRouter()
    {
    	$this->bootstrap('frontController');
    	$frontController = $this->getResource('frontController');
    	$router = $frontController->getRouter();
        $config = new Zend_Config(include dirname(__FILE__) . '/configs/routes.php');
        $router->addConfig($config);
    }
}
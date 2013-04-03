<?php

class Service_Abstract
{
	/**
	 * Contient les tables DB
	 *
	 * @var array
	 */
	protected $_table;

	protected $_services = array();

	public function __construct()
	{
		$this->_initTable();
	}

	protected function _initTable()
	{
		$className = substr(get_class($this), 8);
		$tableName = 'Model_DbTable_' . $className;
		$this->_table = new $tableName();
	}

	public function getService($serviceName)
	{
		$serviceName = strtolower($serviceName);
		if (!array_key_exists($serviceName, $this->_services)) {
			$className = 'Service_' . ucfirst($serviceName);
			$this->_services[$serviceName] = new $className();
		}
		return $this->_services[$serviceName];
	}
}
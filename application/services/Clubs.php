<?php

class Service_Clubs extends Service_Abstract
{
	protected $_propertiesResource = array();

	public function __construct()
	{
		parent::__construct();
		$object = $this->_table->createRow()->toArray();
		$this->_propertiesResource = array_keys($object);
	}

	public function fetchAll($params = array())
	{
		$params = array_intersect_key($params, array_flip($this->_propertiesResource));
		$where = array();
		foreach ($params as $key => $value) {
			if (!is_array($value)) {
				$key .= ' = ?';
			} else {
				$key .= $value[static::OPERATOR] . ' ?';
				$value = $value[static::VALUE];
			}
			$where[$key] = $value;
		}
		return $this->_table->fetchAll($where)->toArray();
	}

	public function fetch($id)
	{
		$record = $this->_table->find($id);
		if ($record->count() > 0) {
			return $record->current();
		}
		throw new Exception('resource unknown');
	}

	public function getLeague($id)
	{
		$serviceLeagues = $this->getService('Leagues');
		return $serviceLeagues->fetch(array('id' => $id));
	}
}
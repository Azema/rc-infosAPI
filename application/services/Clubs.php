<?php

class Service_Clubs extends Service_Abstract
{
	public function fetchAll($params = array())
	{
		return $this->_table->fetchAll()->toArray();
	}

	public function fetch($id)
	{
		$record = $this->_table->find($id);
		if ($record->count() > 0) {
			return $record->current();
		}
		throw new Exception('resource unknown');
	}
}
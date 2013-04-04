<?php

class Service_Leagues extends Service_Abstract
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

	public function getClubs($id)
	{
		$serviceClubs = $this->getService('Clubs');
		return $serviceClubs->fetchAll(array('leagueId' => $id));
	}
}
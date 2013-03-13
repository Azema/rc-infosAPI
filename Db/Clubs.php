<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

namespace Rca\Db;

/**
 * @see \Rca\Db\AbstractDb
 */
//require_once 'Db/AbstractDb.php';

/**
 * Classe abstraite des classes de tables de BDD
 *
 * @category  Rca
 * @package   Db
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class Clubs extends \Rca\Db\AbstractDb
{
	protected $_name = 'clubs';

	protected $_objectName = '\Rca\Model\Club';

	protected function _init()
	{
		unset($this->_map['leg_id']);
		$this->_map['leagueId'] = 'clb_leg_id';
	}

	public function insert($data, $ignore = false)
	{
		if (empty($data)) {
			return 0;
		}
		return $this->insertMulti(array($data), $ignore);
	}

	public function insertMulti($data, $ignore = false)
	{
		$primary = reset($this->_primary);
		foreach ($data as $key => $value) {
			if (array_key_exists($primary, $value)) {
				unset($data[$key][$primary]);
			}
			$data[$key]['createdAt'] = date('c');
			if (array_key_exists('updatedAt', $value)) {
				unset($data[$key]['updatedAt']);
			}
		}
		return parent::insertMulti($data, $ignore);
	}
}

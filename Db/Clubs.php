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
require_once 'Db/AbstractDb.php';

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
class Clubs extends AbstractDb
{
	protected $_name = 'clubs';

	protected $_modelClass = '\Rca\Model\Club';

	public function __construct()
	{
		parent::__construct();
		$this->_map['clb_leg_id'] = 'leagueId';
	}
}

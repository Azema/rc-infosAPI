<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

namespace Rca\Model;

/**
 * @see \Rca\Model\AbstractModel
 */
require_once 'Model/AbstractModel.php';

/**
 * Classe représentant un pilote
 *
 * @category  Rca
 * @package   Model
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class Driver extends AbstractModel
{
	/**
	 * Identifiant du pilote
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Prénom
	 *
	 * @var string
	 */
	protected $firstName;

	/**
	 * Nom
	 *
	 * @var string
	 */
	protected $lastName;

	/**
	 * Identifiant du club
	 *
	 * @var integer
	 */
	protected $clubId;

	/**
	 * Le numéro de licence
	 *
	 * @var string
	 */
	protected $license;

	/**
	 * Type de licence
	 *
	 * @var string
	 */
	protected $licenseType;

	/**
	 * Adresse mail
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * Téléphone
	 *
	 * @var string
	 */
	protected $phone;

	/**
	 * Date de création
	 *
	 * @var string
	 */
	protected $createdAt;

	/**
	 * Date de mise à jour
	 *
	 * @var string
	 */
	protected $updatedAt;

	/**
	 * Indique si le pilote est licencié
	 *
	 * @return boolean
	 */
	public function isLicensee()
	{
		return !empty($this->licence);
	}

	/**
	 * Indique si le pilote est affilié à un club
	 *
	 * @return boolean
	 */
	public function hasClub()
	{
		return !empty($this->clubId);
	}

	/**
	 * Retourne le pilote sous forme de chaine de caractère
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->firstName . ' ' . $this->lastName;
	}
}
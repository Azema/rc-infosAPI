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
 * Classe représentant un club
 *
 * @category  Rca
 * @package   Model
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class Club extends AbstractModel
{
	/**
	 * Identifiant du club
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Nom du club
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Adresse du club (nom de rue)
	 *
	 * @var string
	 */
	protected $address;

	/**
	 * Code postal
	 *
	 * @var string
	 */
	protected $postCode;

	/**
	 * Ville
	 *
	 * @var string
	 */
	protected $city;

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
	 * Coordonnées GPS
	 *
	 * @var string
	 */
	protected $gps;

	/**
	 * Adresse du site Web
	 *
	 * @var string
	 */
	protected $siteWeb;

	/**
	 * L'identifiant de la ligue affilié
	 *
	 * @var integer
	 */
	protected $leagueId;

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
}
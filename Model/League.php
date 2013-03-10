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
 * Classe représentant une ligue
 *
 * @category  Rca
 * @package   Model
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class League extends AbstractModel
{
	/**
	 * Identifiant de la ligue
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Nom de la ligue
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * President de la ligue
	 *
	 * @var string
	 */
	protected $president;

	/**
	 * Adresse de la ligue (nom de rue)
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
	 * Adresse du site Web
	 *
	 * @var string
	 */
	protected $siteWeb;

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
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
 * Classe représentant une piste
 *
 * @category  Rca
 * @package   Model
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class Track extends AbstractModel
{
	/**
	 * Identifiant de la piste
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Identifiant du club
	 *
	 * @var integer
	 */
	protected $clubId;

	/**
	 * Type de piste (TT/bitume)
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Types de moteurs autorisés
	 *
	 * @var string
	 */
	protected $motors;

	/**
	 * Revêtement de la piste
	 *
	 * @var string
	 */
	protected $coating;

	/**
	 * Longueur de la piste
	 *
	 * @var integer
	 */
	protected $length;

	/**
	 * Largeur de la piste
	 *
	 * @var integer
	 */
	protected $width;

	/**
	 * Adresse de la piste (nom de rue)
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
	 * Coordonnées GPS
	 *
	 * @var string
	 */
	protected $gps;

	/**
	 * Equipements sur la piste
	 *
	 * @var string
	 */
	protected $equipments;

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
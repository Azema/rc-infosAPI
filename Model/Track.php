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
	 * @var array
	 */
	protected $motors = array();

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

    /**
     * Le club à lequel est rattaché la piste
     *
     * @var \Rca\Model\Club
     */
    protected $club;

    /**
     * Permet de définir les moteurs autorisés sur la piste
     *
     * @param array $motors Les types de moteurs
     *
     * @return \Rca\Model\Track
     */
    public function setMotors(array $motors = array())
    {
        $this->motors = (array)$motors;
        return $this;
    }

    /**
     * Permet d'ajouter un ou plusieurs moteurs
     *
     * @param string|array $motors Le(s) type(s) de moteur(s)
     *
     * @return \Rca\Model\Track
     */
    public function addMotor($motor)
    {
        if (is_string($motor)) {
            $motor = array($motor);
        }
        $this->motors = array_merge($this->motors, $motor);
        return $this;
    }

    /**
     * Indique si le club est géolocalisé
     *
     * @return boolean
     */
    public function isLocated()
    {
        return !empty($this->gps);
    }

    /**
     * Indique si la piste est rattachée à un club
     *
     * @return boolean
     */
    public function hasClub()
    {
        return !empty($this->clubId);
    }

    /**
     * Indique si le moteur passé en paramètre est autorisé sur la piste
     *
     * @param string $motor Le type de moteur
     *
     * @return boolean
     */
    public function motorAllowed($motor)
    {
        return in_array((string)$motor, $this->motors);
    }
}

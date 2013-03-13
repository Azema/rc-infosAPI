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
 * Classe abstraite des modèles
 *
 * @category  Rca
 * @package   Model
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
abstract class AbstractModel
{
	public function __construct($data = array())
	{
		if (is_array($data)) {
			if (array_key_exists('data', $data)) {
				$this->setFromArray($data['data']);
			}
		}
	}
	/**
	 * Méthode magique pour définir les valeurs des propriétés du modèle
	 * Appelle les méthodes setter si elles existent
	 *
	 * @param string $name  La propriété à définir
	 * @param mixed  $value La valeur
	 *
	 * @return void
	 */
	public function __set($name, $value)
	{
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->{$method}($value);
        } elseif (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
	}

	/**
	 * Retourne la valeur d'une propriété du modèle
	 * Appelle les méthodes getter si elles existent
	 *
	 * @param string $name La propriété appelée
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$method = 'get' . ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->{$method}();
		} elseif (property_exists($this, $name)) {
			return $this->{$name};
		}
		return null;
    }

    /**
     * Remet la propriété à null
     *
     * @param string $name La propriété
     *
     * @return void
     */
    public function __unset($name)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = null;
        }
    }

    /**
     * Indique si la propriété existe dans l'objet
     *
     * @param string $name Le nom de la propriété
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->{$name});
    }

	/**
	 * Rempli l'objet courant à partir d'un tableau de données
	 *
	 * @param array $data Le tableau de données (property => $value)
	 *
	 * @return \Rca\Model\AbstractModel
	 */
	public function setFromArray($data)
	{
        foreach ($data as $key => $value) {
			$this->{$key} = $value;
		}
		return $this;
	}

	/**
	 * Définit l'identifiant du modèle
	 *
	 * @param int $id L'identifiant
	 *
	 * @return \Rca\Model\AbstractModel
	 */
	public function setId($id)
	{
		$this->id = (int)$id;
		return $this;
	}
}

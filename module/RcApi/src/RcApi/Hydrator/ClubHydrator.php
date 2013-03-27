<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RcApi\Hydrator;

use ReflectionMethod;
use Traversable;
use Zend\Stdlib\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\HydratorOptionsInterface;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;
use Zend\Stdlib\Hydrator\Filter\FilterProviderInterface;
use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
use Zend\Stdlib\Hydrator\Filter\GetFilter;
use Zend\Stdlib\Hydrator\Filter\HasFilter;
use Zend\Stdlib\Hydrator\Filter\IsFilter;
use Zend\Stdlib\Hydrator\Filter\NumberOfParameterFilter;

class ClubHydrator extends ClassMethods implements HydratorOptionsInterface
{
	protected $prefix = 'clb_';

	/**
     * @param  array|Traversable $options
     * @return ClassMethods
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
    	parent::setOptions($options);
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'The options parameter must be an array or a Traversable'
            );
        }
        if (isset($options['prefix'])) {
            $this->setPrefix($options['prefix']);
        }

        return $this;
    }

	/**
	 * Définit le prefix des colonnes
	 *
	 * @param string $prefix Le prefix des colonnes
	 *
	 * @return  ClubHydrator
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}

	/**
	 * Retourne le prefix définit
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
     * Extract values from an object with class methods
     *
     * Extracts the getter/setter of the given $object.
     *
     * @param  object                           $object
     * @return array
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function extract($object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)', __METHOD__
            ));
        }

        $filter = null;
        if ($object instanceof FilterProviderInterface) {
            $filter = new FilterComposite(
                array($object->getFilter()),
                array(new MethodMatchFilter("getFilter"))
            );
        } else {
            $filter = $this->filterComposite;
        }

        $transform = function ($letters) {
            $letter = array_shift($letters);

            return '_' . strtolower($letter);
        };
        $attributes = array();
        $methods = get_class_methods($object);

        foreach ($methods as $method) {
            if (
                !$filter->filter(
                    get_class($object) . '::' . $method
                )
            ) {
                continue;
            }

            $reflectionMethod = new ReflectionMethod(get_class($object) . '::' . $method);
            if ($reflectionMethod->getNumberOfParameters() > 0) {
                continue;
            }

            $attribute = $method;
            if (preg_match('/^get/', $method)) {
                $attribute = substr($method, 3);
                $attribute = lcfirst($attribute);
            }
            if ($this->prefix) {
            	$attribute = $this->prefix . $attribute;
            }
            $attributes[$attribute] = $this->extractValue($attribute, $object->$method());
        }

        return $attributes;
    }

    /**
     * Hydrate an object by populating getter/setter methods
     *
     * Hydrates an object by getter/setter methods of the object.
     *
     * @param  array                            $data
     * @param  object                           $object
     * @return object
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s expects the provided $object to be a PHP object)', __METHOD__
            ));
        }

        $transform = function ($letters) {
            $letter = substr(array_shift($letters), 1, 1);

            return ucfirst($letter);
        };

        $lenPrefix = strlen($this->prefix);
        foreach ($data as $property => $value) {
        	if (strpos($property, $this->prefix) !== false) {
        		$property = substr($property, $lenPrefix);
        	}
        	if ($property == 'leg_id') {
        		$property = 'leagueId';
        	}
            $method = 'set' . ucfirst($property);
            if (method_exists($object, $method)) {
                $value = $this->hydrateValue($property, $value);

                $object->$method($value);
            }
        }

        return $object;
    }
}
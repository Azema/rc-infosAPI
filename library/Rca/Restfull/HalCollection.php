<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

/**
 * Model a collection for use with HAL payloads
 */
class Rca_Restfull_HalCollection implements Rca_Restfull_LinkCollectionAwareInterface
{
    /**
     * Additional attributes to render with resource
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * @var array|Traversable|\Zend\Paginator\Paginator
     */
    protected $collection;

    /**
     * Name of collection (used to identify it in the "_embedded" object)
     *
     * @var string
     */
    protected $collectionName = 'items';

    /**
     * @var string
     */
    protected $collectionRoute;

    /**
     * @var array
     */
    protected $collectionRouteOptions = array();

    /**
     * @var array
     */
    protected $collectionRouteParams = array();

    /**
     * @var LinkCollection
     */
    protected $links;

    /**
     * Current page
     *
     * @var int
     */
    protected $page = 1;

    /**
     * Number of resources per page
     *
     * @var int
     */
    protected $pageSize = 30;

    /**
     * @var LinkCollection
     */
    protected $resourceLinks;

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * @var array
     */
    protected $resourceRouteOptions = array();

    /**
     * @var array
     */
    protected $resourceRouteParams = array();

    /**
     * @param  array|Traversable|\Zend\Paginator\Paginator $collection
     * @param  string $collectionRoute
     * @param  string $resourceRoute
     * @throws Exception
     */
    public function __construct($collection, $resourceRoute = null, $resourceRouteParams = null, $resourceRouteOptions = null)
    {
        if (!is_array($collection) && !$collection instanceof Traversable) {
            throw new Exception(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($collection) ? get_class($collection) : gettype($collection))
            ));
        }

        $this->collection = $collection;

        if (null !== $resourceRoute) {
            $this->setResourceRoute($resourceRoute);
        }
        if (null !== $resourceRouteParams) {
            $this->setResourceRouteParams($resourceRouteParams);
        }
        if (null !== $resourceRouteOptions) {
            $this->setResourceRouteOptions($resourceRouteOptions);
        }
    }

    /**
     * Proxy to properties to allow read access
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        $names = array(
            'attributes'               => 'attributes',
            'collection'               => 'collection',
            'collectionname'           => 'collectionName',
            'collection_name'          => 'collectionName',
            'collectionroute'          => 'collectionRoute',
            'collection_route'         => 'collectionRoute',
            'collectionrouteoptions'   => 'collectionRouteOptions',
            'collection_route_options' => 'collectionRouteOptions',
            'collectionrouteparams'    => 'collectionRouteParams',
            'collection_route_params'  => 'collectionRouteParams',
            'links'                    => 'links',
            'resourcelinks'            => 'resourceLinks',
            'resource_links'           => 'resourceLinks',
            'resourceroute'            => 'resourceRoute',
            'resource_route'           => 'resourceRoute',
            'resourcerouteoptions'     => 'resourceRouteOptions',
            'resource_route_options'   => 'resourceRouteOptions',
            'resourcerouteparams'      => 'resourceRouteParams',
            'resource_route_params'    => 'resourceRouteParams',
            'page'                     => 'page',
            'pagesize'                 => 'pageSize',
            'page_size'                => 'pageSize',
        );
        $name = strtolower($name);
        if (!in_array($name, array_keys($names))) {
            throw new InvalidArgumentException(sprintf(
                'Invalid property name "%s"',
                $name
            ));
        }
        $prop = $names[$name];
        return $this->{$prop};
    }

    /**
     * Set additional attributes to render as part of resource
     *
     * @param  array $attributes
     * @return Rca_Restfull_HalCollection
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Set the collection name (for use within the _embedded object)
     *
     * @param  string $name
     * @return Rca_Restfull_HalCollection
     */
    public function setCollectionName($name)
    {
        $this->collectionName = (string) $name;
        return $this;
    }

    /**
     * Set the collection route; used for generating pagination links
     *
     * @param  string $route
     * @return Rca_Restfull_HalCollection
     */
    public function setCollectionRoute($route)
    {
        $this->collectionRoute = (string) $route;
        return $this;
    }

    /**
     * Set options to use with the collection route; used for generating pagination links
     *
     * @param  array|Traversable $options
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException
     */
    public function setCollectionRouteOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = static::iteratorToArray($options);
        }
        if (!is_array($options)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }
        $this->collectionRouteOptions = $options;
        return $this;
    }

    /**
     * Convert an iterator to an array.
     *
     * Converts an iterator to an array. The $recursive flag, on by default,
     * hints whether or not you want to do so recursively.
     *
     * @param  array|Traversable  $iterator     The array or Traversable object to convert
     * @param  bool               $recursive    Recursively check all nested structures
     * @throws InvalidArgumentException if $iterator is not an array or a Traversable object
     * @return array
     */
    public static function iteratorToArray($options, $recursive = true)
    {
        if (!is_array($iterator) && !$iterator instanceof Traversable) {
            throw new InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        if (!$recursive) {
            if (is_array($iterator)) {
                return $iterator;
            }

            return iterator_to_array($iterator);
        }

        if (method_exists($iterator, 'toArray')) {
            return $iterator->toArray();
        }

        $array = array();
        foreach ($iterator as $key => $value) {
            if (is_scalar($value)) {
                $array[$key] = $value;
                continue;
            }

            if ($value instanceof Traversable || is_array($value)) {
                $array[$key] = static::iteratorToArray($value, $recursive);
                continue;
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Set parameters/substitutions to use with the collection route; used for generating pagination links
     *
     * @param  array|Traversable $params
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException
     */
    public function setCollectionRouteParams($params)
    {
        if ($params instanceof Traversable) {
            $params = static::iteratorToArray($params);
        }
        if (!is_array($params)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($params) ? get_class($params) : gettype($params))
            ));
        }
        $this->collectionRouteParams = $params;
        return $this;
    }

    /**
     * Set link collection
     *
     * @param  Rca_Restfull_LinkCollection $links
     * @return self
     */
    public function setLinks(Rca_Restfull_LinkCollection $links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Set current page
     *
     * @param  int $page
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException for non-positive and/or non-integer values
     */
    public function setPage($page)
    {
        if (!is_int($page) && !is_numeric($page)) {
            throw new InvalidArgumentException(sprintf(
                'Page must be an integer; received "%s"',
                gettype($page)
            ));
        }

        $page = (int) $page;
        if ($page < 1) {
            throw new InvalidArgumentException(sprintf(
                'Page must be a positive integer; received "%s"',
                $page
            ));
        }

        $this->page = $page;
        return $this;
    }

    /**
     * Set page size
     *
     * @param  int $size
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException for non-positive and/or non-integer values
     */
    public function setPageSize($size)
    {
        if (!is_int($size) && !is_numeric($size)) {
            throw new InvalidArgumentException(sprintf(
                'Page size must be an integer; received "%s"',
                gettype($size)
            ));
        }

        $size = (int) $size;
        if ($size < 1) {
            throw new InvalidArgumentException(sprintf(
                'size must be a positive integer; received "%s"',
                $size
            ));
        }

        $this->pageSize = $size;
        return $this;
    }

    /**
     * Set default set of links to use for resources
     *
     * @param  Rca_Restfull_LinkCollection $links
     * @return self
     */
    public function setResourceLinks(Rca_Restfull_LinkCollection $links)
    {
        $this->resourceLinks = $links;
        return $this;
    }

    /**
     * Set the resource route
     *
     * @param  string $route
     * @return Rca_Restfull_HalCollection
     */
    public function setResourceRoute($route)
    {
        $this->resourceRoute = (string) $route;
        return $this;
    }

    /**
     * Set options to use with the resource route
     *
     * @param  array|Traversable $options
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException
     */
    public function setResourceRouteOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = static::iteratorToArray($options);
        }
        if (!is_array($options)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }
        $this->resourceRouteOptions = $options;
        return $this;
    }

    /**
     * Set parameters/substitutions to use with the resource route
     *
     * @param  array|Traversable $params
     * @return Rca_Restfull_HalCollection
     * @throws InvalidArgumentException
     */
    public function setResourceRouteParams($params)
    {
        if ($params instanceof Traversable) {
            $params = static::iteratorToArray($params);
        }
        if (!is_array($params)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($params) ? get_class($params) : gettype($params))
            ));
        }
        $this->resourceRouteParams = $params;
        return $this;
    }

    /**
     * Get link collection
     *
     * @return Rca_Restfull_LinkCollection
     */
    public function getLinks()
    {
        if (!$this->links instanceof Rca_Restfull_LinkCollection) {
            $this->setLinks(new Rca_Restfull_LinkCollection());
        }
        return $this->links;
    }

    /**
     * Retrieve default resource links, if any
     *
     * @return null|Rca_Restfull_LinkCollection
     */
    public function getResourceLinks()
    {
        return $this->resourceLinks;
    }
}

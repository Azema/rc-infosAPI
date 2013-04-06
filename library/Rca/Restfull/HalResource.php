<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

class Rca_Restfull_HalResource implements Rca_Restfull_LinkCollectionAwareInterface
{
    protected $id;

    /**
     * @var Rca_Restfull_LinkCollection
     */
    protected $links;

    protected $resource;

    /**
     * @param  object|array $resource
     * @param  mixed $id
     * @throws Exception if resource is not an object or array
     */
    public function __construct($resource, $id)
    {
        if (!is_object($resource) && !is_array($resource)) {
            throw new Exception();
        }

        $this->resource    = $resource;
        $this->id          = $id;
    }

    /**
     * Retrieve properties
     *
     * @param  string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        $names = array(
            'resource'     => 'resource',
            'id'           => 'id',
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

    public function toArray()
    {
        if (is_array($this->resource)) {
            return $this->resource;
        } elseif (is_object($this->resource) && method_exists($this->resource, 'extract')) {
            return $this->resource->extract();
        } elseif (is_object($this->resource) && method_exists($this->resource, 'toArray')) {
            return $this->resource->toArray();
        }
        return (array)$this->resource;
    }
}

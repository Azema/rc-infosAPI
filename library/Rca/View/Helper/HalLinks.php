<?php
/**
 * @link      https://github.com/weierophinney/PhlyRestfully for the canonical source repository
 * @copyright Copyright (c) 2013 Matthew Weier O'Phinney
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @package   PhlyRestfully
 */

/**
 * Generate links for use with HAL payloads
 */
class Rca_View_Helper_HalLinks extends Zend_View_Helper_Abstract
{
    /**
     * Default hydrator to use if no hydrator found for a specific resource class.
     *
     * @var HydratorInterface
     */
    protected $defaultHydrator;

    /**
     * Map of resource classes => hydrators
     *
     * @var HydratorInterface[]
     */
    protected $hydrators = array();

    /**
     * @var ServerUrl
     */
    protected $serverUrlHelper;

    /**
     * @var Url
     */
    protected $urlHelper;

    public function getIdFromResource($resource)
    {
        if (!is_array($resource)) {
            return false;
        }

        if (array_key_exists('id', $resource)) {
            return $resource['id'];
        }

        return false;
    }

    /**
     * @param ServerUrl $helper
     */
    public function setServerUrlHelper(Zend_View_Helper_ServerUrl $helper)
    {
        $this->serverUrlHelper = $helper;
    }

    /**
     * @param Zend_View_Helper_Url $helper
     */
    public function setUrlHelper(Zend_View_Helper_Url $helper)
    {
        $this->urlHelper = $helper;
    }

    /**
     * Map a resource class to a specific hydrator instance
     *
     * @param  string $class
     * @param  HydratorInterface $hydrator
     * @return RestfulJsonRenderer
     */
    public function addHydrator($class, $hydrator)
    {
        $this->hydrators[strtolower($class)] = $hydrator;
        return $this;
    }

    /**
     * Set the default hydrator to use if none specified for a class.
     *
     * @param  HydratorInterface $hydrator
     * @return RestfulJsonRenderer
     */
    public function setDefaultHydrator($hydrator)
    {
        $this->defaultHydrator = $hydrator;
        return $this;
    }

    /**
     * Retrieve a hydrator for a given resource
     *
     * If the resource has a mapped hydrator, returns that hydrator. If not, and
     * a default hydrator is present, the default hydrator is returned.
     * Otherwise, a boolean false is returned.
     *
     * @param  object $resource
     * @return HydratorInterface|false
     */
    public function getHydratorForResource($resource)
    {
        $class = strtolower(get_class($resource));
        if (isset($this->hydrators[$class])) {
            return $this->hydrators[$class];
        }

        /*
        if ($this->defaultHydrator instanceof HydratorInterface) {
            return $this->defaultHydrator;
        }*/

        return false;
    }

    /**
     * "Render" a HalCollection
     *
     * Injects pagination links, if the composed collection is a Paginator, and
     * then loops through the collection to create the data structure representing
     * the collection.
     *
     * For each resource in the collection, the event "renderCollection.resource" is
     * triggered, with the following parameters:
     *
     * - "collection", which is the $halCollection passed to the method
     * - "resource", which is the current resource
     * - "route", the resource route that will be used to generate links
     * - "routeParams", any default routing parameters/substitutions to use in URL assembly
     * - "routeOptions", any default routing options to use in URL assembly
     *
     * This event can be useful particularly when you have multi-segment routes
     * and wish to ensure that route parameters are injected, or if you want to
     * inject query or fragment parameters.
     *
     * Event parameters are aggregated in an ArrayObject, which allows you to
     * directly manipulate them in your listeners:
     *
     * <code>
     * $params = $e->getParams();
     * $params['routeOptions']['query'] = array('format' => 'json');
     * </code>
     *
     * @param  HalCollection $halCollection
     * @return array|ApiProblem Associative array representing the payload to render; returns ApiProblem if error in pagination occurs
     */
    public function renderCollection(Rca_Restfull_HalCollection $halCollection)
    {
        $collection     = $halCollection->collection;
        $collectionName = $halCollection->collectionName;

        if ($collection instanceof Zend_Paginator) {
            $status = $this->injectPaginationLinks($halCollection);
            if ($status instanceof Rca_Restfull_ApiProblem) {
                return $status;
            }
        }

        $payload = $halCollection->attributes;
        $payload['_links']    = $this->fromResource($halCollection);
        $payload['_embedded'] = array(
            $collectionName => array(),
        );

        $resourceRoute        = $halCollection->resourceRoute;
        $resourceRouteParams  = $halCollection->resourceRouteParams;
        $resourceRouteOptions = $halCollection->resourceRouteOptions;
        foreach ($collection as $resource) {
            $eventParams = new ArrayObject(array(
                'collection'   => $halCollection,
                'resource'     => $resource,
                'route'        => $resourceRoute,
                'routeParams'  => $resourceRouteParams,
                'routeOptions' => $resourceRouteOptions,
            ));
            //$events->trigger('renderCollection.resource', $this, $eventParams);

            $resource = $eventParams['resource'];

            if (!is_array($resource)) {
                $resource = $this->convertResourceToArray($resource);
            }

            foreach ($resource as $key => $value) {
                if (!$value instanceof Rca_Restfull_HalResource) {
                    continue;
                }
                $this->extractEmbeddedHalResource($resource, $key, $value);
            }

            $id = $this->getIdFromResource($resource);
            if (!$id) {
                // Cannot handle resources without an identifier
                // Return as-is
                $payload['_embedded'][$collectionName][] = $resource;
                continue;
            }

            if ($eventParams['resource'] instanceof Rca_Restfull_LinkCollectionAwareInterface) {
                $links = $eventParams['resource']->getLinks();
            } else {
                $links = new Rca_Restfull_LinkCollection();
            }

            if (!$links->has('self')) {
                $selfLink = new Rca_Restfull_Link('self');
                $selfLink->setRoute(
                    $eventParams['route'],
                    array_merge($eventParams['routeParams'], array('id' => $id)),
                    $eventParams['routeOptions']
                );
                $links->add($selfLink);
            }

            $resource['_links'] = $this->fromLinkCollection($links);
            $payload['_embedded'][$collectionName][] = $resource;
        }

        return $payload;
    }

    /**
     * Render an individual resource
     *
     * Creates a hash representation of the HalResource. The resource is first
     * converted to an array, and its associated links are injected as the
     * "_links" member. If any members of the resource are themselves
     * HalResource objects, they are extracted into an "_embedded" hash.
     *
     * @param  HalResource $halResource
     * @return array
     */
    public function renderResource(Rca_Restfull_HalResource $halResource)
    {
        $resource = $halResource->resource;
        $id       = $halResource->id;
        $links    = $this->fromResource($halResource);

        if (!is_array($resource)) {
            $resource = $this->convertResourceToArray($resource);
        }

        foreach ($resource as $key => $value) {
            if (!$value instanceof Rca_Restfull_LinkCollectionAwareInterface) {
                continue;
            } elseif ($value instanceof Rca_Restfull_HalResource) {
                $this->extractEmbeddedHalResource($resource, $key, $value);
            } elseif ($value instanceof Rca_Restfull_HalCollection) {
                $this->extractEmbeddedHalCollection($resource, $key, $value);
            }
        }

        $resource['_links'] = $links;

        return $resource;
    }

    /**
     * Create a fully qualified URI for a link
     *
     * Triggers the "createLink" event with the route, id, resource, and a set of
     * params that will be passed to the route; listeners can alter any of the
     * arguments, which will then be used by the method to generate the url.
     *
     * @param  string $route
     * @param  null|false|int|string $id
     * @param  null|mixed $resource
     * @return string
     */
    public function createLink($route, $id = null, $resource = null)
    {
        $params             = new ArrayObject();
        $reUseMatchedParams = true;

        if (false === $id) {
            $reUseMatchedParams = false;
        } elseif (null !== $id) {
            $params['id'] = $id;
        }

        //$events      = $this->getEventManager();
        $eventParams = $events->prepareArgs(array(
            'route'    => $route,
            'id'       => $id,
            'resource' => $resource,
            'params'   => $params,
        ));
        //$events->trigger(__FUNCTION__, $this, $eventParams);
        $route = $eventParams['route'];

        $path = $this->urlHelper->url($params->getArrayCopy(), $route, $reUseMatchedParams);

        if (substr($path, 0, 4) == 'http') {
            return $path;
        }

        return $this->serverUrlHelper->serverUrl($path);
    }

    /**
     * Generate HAL links from a LinkCollection
     *
     * @param  Rca_Restfull_LinkCollection $collection
     * @return array
     */
    public function fromLinkCollection(Rca_Restfull_LinkCollection $collection)
    {
        $links = array();
        foreach($collection as $rel => $linkDefinition) {
            if ($linkDefinition instanceof Rca_Restfull_Link) {
                $links[$rel] = $this->fromLink($linkDefinition);
                continue;
            }
            if (!is_array($linkDefinition)) {
                throw new Exception(sprintf(
                    'Link object for relation "%s" in resource was malformed; cannot generate link',
                    $rel
                ));
            }

            $aggregate = array();
            foreach ($linkDefinition as $subLink) {
                if (!$subLink instanceof Rca_Restfull_Link) {
                    throw new Exception(sprintf(
                        'Link object aggregated for relation "%s" in resource was malformed; cannot generate link',
                        $rel
                    ));
                }
                $aggregate[] = $this->fromLink($subLink);
            }
            $links[$rel] = $aggregate;
        }
        return $links;
    }

    /**
     * Create HAL links "object" from a resource/collection
     *
     * @param  Rca_Restfull_LinkCollectionAwareInterface $resource
     * @return array
     */
    public function fromResource(Rca_Restfull_LinkCollectionAwareInterface $resource)
    {
        return $this->fromLinkCollection($resource->getLinks());
    }

    /**
     * Generate HAL links for a paginated collection
     *
     * @param  HalCollection $halCollection
     * @return array
     */
    protected function injectPaginationLinks(Rca_Restfull_HalCollection $halCollection)
    {
        $collection = $halCollection->collection;
        $page       = $halCollection->page;
        $pageSize   = $halCollection->pageSize;
        $route      = $halCollection->collectionRoute;
        $params     = $halCollection->collectionRouteParams;
        $options    = $halCollection->collectionRouteOptions;

        $collection->setItemCountPerPage($pageSize);
        $collection->setCurrentPageNumber($page);

        $count = count($collection);
        if (!$count) {
            return true;
        }

        if ($page < 1 || $page > $count) {
            return new Rca_Restfull_ApiProblem(409, 'Invalid page provided');
        }
        $route .= '_paginator';

        $links = $halCollection->getLinks();
        $next  = ($page < $count) ? $page + 1 : false;
        $prev  = ($page > 1)      ? $page - 1 : false;

        // self link
        $link = new Rca_Restfull_Link('self');
        $link->setRoute($route);
        $link->setRouteParams($params);
        $link->setRouteOptions(array_merge($options, array('page' => $page)));
        $links->add($link, true);

        // first link
        $link = new Rca_Restfull_Link('first');
        $link->setRoute($route);
        $link->setRouteParams($params);
        $link->setRouteOptions($options);
        $links->add($link);

        // last link
        $link = new Rca_Restfull_Link('last');
        $link->setRoute($route);
        $link->setRouteParams($params);
        $link->setRouteOptions(array_merge($options, array('page' => $count)));
        $links->add($link);

        // prev link
        if ($prev) {
            $link = new Rca_Restfull_Link('prev');
            $link->setRoute($route);
            $link->setRouteParams($params);
            $link->setRouteOptions(array_merge($options, array('page' => $prev)));
            $links->add($link);
        }

        // next link
        if ($next) {
            $link = new Rca_Restfull_Link('next');
            $link->setRoute($route);
            $link->setRouteParams($params);
            $link->setRouteOptions(array_merge($options, array('page' => $next)));
            $links->add($link);
        }

        return true;
    }

    /**
     * Create a URL from a Link
     *
     * @param  Link $linkDefinition
     * @return string
     * @throws Exception\DomainException if Link is incomplete
     */
    protected function fromLink(Rca_Restfull_Link $linkDefinition)
    {
        if (!$linkDefinition->isComplete()) {
            throw new Exception(sprintf(
                'Link from resource provided to %s was incomplete; must contain a URL or a route',
                __METHOD__
            ));
        }

        if ($linkDefinition->hasUrl()) {
            return array(
                'href' => $linkDefinition->getUrl(),
            );
        }

        $path = $this->urlHelper->url(
            array_merge($linkDefinition->getRouteParams(), $linkDefinition->getRouteOptions()),
            $linkDefinition->getRoute(),
            true
        );

        if (substr($path, 0, 4) == 'http') {
            return $path;
        }

        return array(
            'href' => $this->serverUrlHelper->serverUrl($path),
        );
    }

    /**
     * Extracts and renders a HalResource and embeds it in the parent
     * representation
     *
     * Removes the key from the parent representation, and creates a
     * representation for the key in the _embedded object.
     *
     * @param  array $parent
     * @param  string $key
     * @param  Rca_Restfull_HalResource $resource
     */
    protected function extractEmbeddedHalResource(array &$parent, $key, Rca_Restfull_HalResource $resource)
    {
        $rendered = $this->renderResource($resource);
        if (!isset($parent['_embedded'])) {
            $parent['_embedded'] = array();
        }
        $parent['_embedded'][$key] = $rendered;
        unset($parent[$key]);
    }

    /**
     * Extracts and renders a HalCollection and embeds it in the parent
     * representation
     *
     * Removes the key from the parent representation, and creates a
     * representation for the key in the _embedded object.
     *
     * @param  array $parent
     * @param  string $key
     * @param  Rca_Restfull_HalCollection $resource
     */
    protected function extractEmbeddedHalCollection(array &$parent, $key, Rca_Restfull_HalCollection $collection)
    {
        $rendered = $this->renderCollection($collection);
        if (!isset($parent['_embedded'])) {
            $parent['_embedded'] = array();
        }
        $parent['_embedded'][$collection->collectionName] = $rendered;
        unset($parent[$key]);
    }

    /**
     * Convert an individual resource to an array
     *
     * @param  object $resource
     * @return array
     */
    protected function convertResourceToArray($resource)
    {
        $hydrator = $this->getHydratorForResource($resource);
        $toArrayExists = method_exists($resource, 'toArray');
        $extractExists = method_exists($resource, 'extract');
        if (!$hydrator && !$toArrayExists && !$extractExists) {
            return (array)$resource;
        } elseif ($extractExists) {
            return $resource->extract();
        } elseif ($toArrayExists) {
            return $resource->toArray();
        }

        return $hydrator->extract($resource);
    }

}

<?php
/**
 * DocBlock
 *
 * @category RcApi
 * @package  RcApi
 * @author   Matthew wieir O'Phinney <toto@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     https://github.com/weierophinney/RcApi
 *
 * PHP 5.3
 */
namespace RcApi;

use PhlyRestfully\HalResource;
use PhlyRestfully\Link;
use PhlyRestfully\LinkCollectionAwareInterface;
use PhlyRestfully\View\RestfulJsonModel;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

/**
 * Classe initialisation du module
 *
 * @category RcApi
 * @package  RcApi
 * @author   Matthew wieir O'Phinney <toto@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     https://github.com/weierophinney/RcApi
 */
class Module
{
    /**
     * Autoloader config
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ),
        ));
    }

    /**
     * config
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * bootstrap
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $events   = $app->getEventManager();
        if ($services->has('Hydrator\ClassMethods')) {
            $services->get('Hydrator\ClassMethods')->setUnderscoreSeparatedKeys(false);
        }
        $events->attach('route', array($this, 'onRoute'), -100);

        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'PhlySimplePage\PageController',
            'dispatch',
            array($this, 'onDispatchDocs'),
            -1
        );
    }

    /**
     * route
     *
     * @param Event $e Event
     *
     * @return void
     */
    public function onRoute($e)
    {
        $controllers = 'RcApi\ClubsResourceController';

        $matches = $e->getRouteMatch();
        if (!$matches) {
            return;
        }
        $controller = $matches->getParam('controller', false);
        if ($controller != $controllers) {
            return;
        }

        $app          = $e->getTarget();
        $services     = $app->getServiceManager();
        $events       = $app->getEventManager();
        $sharedEvents = $events->getSharedManager();

        // Add a "Link" header pointing to the documentation
        $sharedEvents->attach(
            $controllers,
            'dispatch',
            array($this, 'setDocumentationLink'),
            10
        );

        // Add a "describedby" relation to resources
        $sharedEvents->attach(
            $controllers,
            array(
                'getList.post',
                'get.post',
                'create.post',
                'patch.post',
                'update.post',
            ),
            array($this, 'setDescribedByRelation')
        );

        // Add metadata to collections
        $sharedEvents->attach(
            $controllers,
            'dispatch',
            array($this, 'onDispatchCollection'),
            -1
        );

        //$sharedEvents->attach($controllers, 'getList.post', function ($e) {
            //$collection = $e->getParam('collection');
            //$collection->setResourceRoute('phpbnl13_status_api/user');
        //});

        // Set a listener on the renderCollection.resource event to ensure
        // individual status links pass in the user to the route.
        $helpers = $services->get('ViewHelperManager');
        $links   = $helpers->get('HalLinks');
        $links->getEventManager()->attach('renderCollection.resource', function ($e) {
            $eventParams = $e->getParams();
            $route       = $eventParams['route'];
            $routeParams = $eventParams['routeParams'];

            if ($route != 'rc_clubs_api/public') {
                return;
            }

            $resource = $eventParams['resource'];

            if ($resource instanceof Club) {
               $eventParams['route'] = 'rc_clubs_api/club';
                $eventParams['routeParams']['id']  = $resource->getId();
                return;
            }

            if (!is_array($resource)) {
                return;
            }

            if (!isset($resource['id'])) {
                return;
            }

            $eventParams['route'] = 'rc_clubs_api/club';
            $eventParams['routeParams']['id']  = $resource['id'];
        });

        // Set the user in the persistence listener
        $persistence = $services->get('RcApi\PersistenceListener');
        if (!$persistence instanceof StatusPersistenceInterface) {
            return;
        }
    }

    /**
     *
     */
    public function onDispatchDocs($e)
    {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        $base  = 'rc_clubs_api/documentation';
        if (strlen($route) < strlen($base)
            || 0 !== strpos($route, $base)
        ) {
            return;
        }

        $model = $e->getResult();
        $model->setTerminal(true);

        $response = $e->getResponse();
        $headers  = $response->getHeaders();

        if ($route == $base) {
            $headers->addHeaderLine('content-type', 'text/x-markdown');
            return;
        }

        $headers->addHeaderLine('content-type', 'application/json');
    }

    /**
     *
     */
    public function setDocumentationLink($e)
    {
        $controller = $e->getTarget();
        $docsUrl    = $controller->halLinks()->createLink('rc_clubs_api/documentation', false);
        $response   = $e->getResponse();
        $response->getHeaders()->addHeaderLine(
            'Link',
            sprintf('<%s>; rel="describedby"', $docsUrl)
        );
    }

    /**
     *
     */
    public function onDispatchCollection($e)
    {
        $result = $e->getResult();
        if (!$result instanceof RestfulJsonModel && $result->isHalCollection()) {
            return;
        }
        $collection = $result->getPayload();

        if (!$collection instanceof HalCollection) {
           return;
        }

        if (!$collection->collection instanceof Paginator) {
            return;
        }
        $collection->setAttributes(array(
            'count'    => $collection->collection->getTotalItemCount(),
            'page'     => $collection->page,
            'per_page' => $collection->pageSize,
        ));
    }

    /**
     *
     */
    public function setDescribedByRelation($e)
    {
        $resource = $e->getParam('resource', false);
        if (!$resource) {
            $resource = $e->getParam('collection', false);
        }

        if (!$resource instanceof LinkCollectionAwareInterface) {
            return;
        }
        $link = new Link('describedby');

        if ($resource instanceof HalResource) {
            $link->setRoute('rc_clubs_api/documentation/clubs');
        } else {
            $link->setRoute('rc_clubs_api/documentation/collection');
        }
        $resource->getLinks()->add($link);
    }
}

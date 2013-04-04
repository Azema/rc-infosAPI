<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Action.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * @see Zend_Controller_Action_HelperBroker
 */
require_once 'Zend/Controller/Action/HelperBroker.php';

/**
 * @see Zend_Controller_Action_Interface
 */
require_once 'Zend/Controller/Action/Interface.php';

/**
 * @see Zend_Controller_Front
 */
require_once 'Zend/Controller/Front.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Rca_Controller_Action_Restfull extends Zend_Controller_Action
{
    const CONTENT_TYPE_JSON = 'json';

    /**
     * @var array
     */
    protected $contentTypes = array(
        self::CONTENT_TYPE_JSON => array(
            'application/hal+json',
            'application/json'
        )
    );

    /**
     * Map of custom HTTP methods and their handlers
     *
     * @var array
     */
    protected $customHttpMethodsMap = array();

    /**
     * HTTP methods we allow for individual resources; used by options()
     *
     * HEAD and OPTIONS are always available.
     *
     * @var array
     */
    protected $resourceHttpOptions = array(
        'DELETE',
        'GET',
        'PATCH',
        'PUT',
    );

    /**
     * Route name that resolves to this resource; used to generate links.
     *
     * @var string
     */
    protected $route;

    /**
     * Number of resources to return per page
     *
     * @var int
     */
    protected $pageSize = 30;

    /**
     * @var Rca_Restfull_ResourceInterface
     */
    protected $resource;

    /**
     * HTTP methods we allow for the resource (collection); used by options()
     *
     * HEAD and OPTIONS are always available.
     *
     * @var array
     */
    protected $collectionHttpOptions = array(
        'GET',
        'POST',
    );

    /**
     * Name of the collections entry in a HalCollection
     *
     * @var string
     */
    protected $collectionName = 'items';

    protected $_service;

    /**
     * Class constructor
     *
     * The request and response objects should be registered with the
     * controller, as should be any additional optional arguments; these will be
     * available via {@link getRequest()}, {@link getResponse()}, and
     * {@link getInvokeArgs()}, respectively.
     *
     * When overriding the constructor, please consider this usage as a best
     * practice and ensure that each is registered appropriately; the easiest
     * way to do so is to simply call parent::__construct($request, $response,
     * $invokeArgs).
     *
     * After the request, response, and invokeArgs are set, the
     * {@link $_helper helper broker} is initialized.
     *
     * Finally, {@link init()} is called as the final action of
     * instantiation, and may be safely overridden to perform initialization
     * tasks; as a general rule, override {@link init()} instead of the
     * constructor to customize an action controller's instantiation.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs Any additional invocation arguments
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->setRequest($request)
             ->setResponse($response)
             ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Zend_Controller_Action_HelperBroker($this);
        $this->init();
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Initialize View object
     *
     * Initializes {@link $view} if not otherwise a Zend_View_Interface.
     *
     * If {@link $view} is not otherwise set, instantiates a new Zend_View
     * object, using the 'views' subdirectory at the same level as the
     * controller directory for the current module as the base directory.
     * It uses this to set the following:
     * - script path = views/scripts/
     * - helper path = views/helpers/
     * - filter path = views/filters/
     *
     * @return Zend_View_Interface
     * @throws Zend_Controller_Exception if base view directory does not exist
     */
    public function initView()
    {
        if (isset($this->view) && $this->view instanceof Rca_View_JsonRenderer) {
            return $this->view;
        }

        $this->view = new Rca_View_JsonRenderer();

        return $this->view;
    }

    /**
     * Render a view
     *
     * Renders a view. By default, views are found in the view script path as
     * <controller>/<action>.phtml. You may change the script suffix by
     * resetting {@link $viewSuffix}. You may omit the controller directory
     * prefix by specifying boolean true for $noController.
     *
     * By default, the rendered contents are appended to the response. You may
     * specify the named body content segment to set by specifying a $name.
     *
     * @see Zend_Controller_Response_Abstract::appendBody()
     * @param  string|null $action Defaults to action registered in request object
     * @param  string|null $name Response object named path segment to use; defaults to null
     * @param  bool $noController  Defaults to false; i.e. use controller name as subdir in which to search for view script
     * @return void
     */
    public function render($action = null, $name = null, $noController = false)
    {
        $this->initView();
        $model = new Rca_View_JsonModel();
        $model->setPayload($action);
        return $this->view->render($model);
    }

    /**
     * Render a given view script
     *
     * Similar to {@link render()}, this method renders a view script. Unlike render(),
     * however, it does not autodetermine the view script via {@link getViewScript()},
     * but instead renders the script passed to it. Use this if you know the
     * exact view script name and path you wish to use, or if using paths that do not
     * conform to the spec defined with getViewScript().
     *
     * By default, the rendered contents are appended to the response. You may
     * specify the named body content segment to set by specifying a $name.
     *
     * @param  string $script
     * @param  string $name
     * @return void
     */
    public function renderScript($script, $name = null)
    {
    }

    /**
     * Set the Accept header criteria for use with the AcceptableViewModelSelector
     *
     * @param array $criteria
     */
    public function setAcceptCriteria(array $criteria)
    {
        $this->acceptCriteria = $criteria;
    }

    /**
     * Set the allowed HTTP OPTIONS for the resource (collection)
     *
     * @param array $options
     */
    public function setCollectionHttpOptions(array $options)
    {
        $this->collectionHttpOptions = $options;
    }

    /**
     * Set the name to which to assign a collection in a HalCollection
     *
     * @param string $name
     */
    public function setCollectionName($name)
    {
        $this->collectionName = (string) $name;
    }

    /**
     * Set the allowed content types for the resource (collection)
     *
     * @param array $contentTypes
     */
    public function setContentTypes(array $contentTypes)
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * Set the default page size for paginated responses
     *
     * @param  int
     */
    public function setPageSize($count)
    {
        $this->pageSize = (int) $count;
    }

    /**
     * Inject the resource with which this controller will communicate.
     *
     * @param Rca_Restfull_ResourceInterface $resource
     */
    public function setResource(Rca_Restfull_ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource
     *
     * @throws Exception If no resource has been set
     *
     * @return Rca_Restfull_ResourceInterface
     */
    public function getResource()
    {
        if ($this->resource === null) {

            throw new Exception('No resource has been set.');
        }

        return $this->resource;
    }

    /**
     * Set the allowed HTTP OPTIONS for a resource
     *
     * @param array $options
     */
    public function setResourceHttpOptions(array $options)
    {
        $this->resourceHttpOptions = $options;
    }

    /**
     * Inject the route name for this resource.
     *
     * @param  string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Create a new resource
     *
     * @param  mixed $data
     * @return mixed
     */
    public function create($data)
    {
        if (!$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_createPre')) {
            $this->_createPre($data);
        }

        try {
            $resource = $this->service->create($data);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            return new Rca_Restfull_ApiProblem($code, $e);
        }

        if (!$resource instanceof Rca_Restfull_HalResource) {
            $id = $this->getIdentifierFromResource($resource);
            if (!$id) {
                return new Rca_Restfull_ApiProblem(
                    422,
                    'No resource identifier present following resource creation.'
                );
            }
            $resource = new Rca_Restfull_HalResource($resource, $id);
        }

        $this->injectSelfLink($resource);

        $response = $this->getResponse();
        $response->setStatusCode(201);
        $response->setHeader(
            'Location',
            $this->_helper->halLinks->createLink($this->route, $resource->id, $resource->resource)
        );

        if (method_exists($this, '_createPost')) {
            $this->_createPost($data, $resource);
        }

        return $resource;
    }

    /**
     * Delete an existing resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function delete($id)
    {
        if ($id && !$this->isMethodAllowedForResource()) {
            return $this->createMethodNotAllowedResponse($this->resourceHttpOptions);
        }
        if (!$id && !$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_deletePre')) {
            $this->_deletePre($id);
        }

        try {
            $result = $this->service->delete($id);
        } catch (Exception $e) {
            return new Rca_Restfull_ApiProblem(500, $e);
        }

        if (!$result) {
            return new Rca_Restfull_ApiProblem(422, 'Unable to delete resource.');
        }

        $response = $this->getResponse();
        $response->setHttpResponseCode(204);

        if (method_exists($this, '_deletePost')) {
            $this->_deletePost($id);
        }

        return $response;
    }

    /**
     * Delete the entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function deleteList($params = array())
    {
        if (!$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_deleteListPre')) {
            $this->_deleteListPre($params);
        }

        try {
            $result = $this->service->deleteList();
        } catch (Exception $e) {
            return new Rca_Restfull_ApiProblem(500, $e);
        }

        if (!$result) {
            return new Rca_Restfull_ApiProblem(422, 'Unable to delete collection.');
        }

        $response = $this->getResponse();
        $response->setHttpResponseCode(204);

        if (method_exists($this, '_deleteListPost')) {
            $this->_deleteListPost($params);
        }

        return $response;
    }

    /**
     * Return single resource
     *
     * @param  mixed $id
     * @return mixed
     */
    public function get($id)
    {
        if (!$this->isMethodAllowedForResource()) {
            return $this->createMethodNotAllowedResponse($this->resourceHttpOptions);
        }

        if (method_exists($this, '_getPre')) {
            $this->_getPre($id);
        }

        try {
            $resource = $this->service->fetch($id);
        } catch (Exception $e) {
            return new Rca_Restfull_ApiProblem(500, $e);
        }

        if (!$resource) {
            return new Rca_Restfull_ApiProblem(404, 'Resource not found.');
        }

        if (!$resource instanceof Rca_Restfull_HalResource) {
            $resource = new Rca_Restfull_HalResource($resource, $id);
        }

        $this->injectSelfLink($resource);
        if (method_exists($this, '_getPost')) {
            $this->_getPost($id, $resource);
        }

        return $resource;
    }

    /**
     * Return list of resources
     *
     * @return mixed
     */
    public function getList($params = array())
    {
        if (!$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_getListPre')) {
            $this->_getListPre($params);
        }

        try {
            $collection = $this->service->fetchAll($params);
        } catch (Exception $e) {
            return new Rca_Restfull_ApiProblem(500, $e);
        }

        if (!$collection instanceof Rca_Restfull_HalCollection) {
            $collection = new Rca_Restfull_HalCollection($collection);
        }
        $this->injectSelfLink($collection);
        $collection->setCollectionRoute($this->route);
        $collection->setResourceRoute($this->route);
        $collection->setPage($this->getRequest()->getQuery('page', 1));
        $collection->setPageSize($this->pageSize);
        $collection->setCollectionName($this->collectionName);

        if (method_exists($this, '_getListPost')) {
            $this->_getListPos($params, $collection);
        }
        return $collection;
    }

    /**
     * Retrieve HEAD metadata for the resource
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  null|mixed $id
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function head($id = null)
    {
        if ($id) {
            return $this->get($id);
        }
        return $this->getList();
    }

    /**
     * Respond to the OPTIONS method
     *
     * Typically, set the Allow header with allowed HTTP methods, and
     * return the response.
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function options()
    {
        $id = $this->getIdentifier($this->getRequest());

        if ($id) {
            $options = $this->resourceHttpOptions;
        } else {
            $options = $this->collectionHttpOptions;
        }

        array_walk($options, function (&$method) {
            $method = strtoupper($method);
        });

        $response = $this->getResponse();
        $response->setHttpResponseCode(204);
        $response->setHeader('Allow', implode(', ', $options), true);

        return $response;
    }

    /**
     * Respond to the PATCH method
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function patch($id, $data)
    {
        if (!$this->isMethodAllowedForResource()) {
            return $this->createMethodNotAllowedResponse($this->resourceHttpOptions);
        }

        if (method_exists($this, '_patchPre')) {
            $this->_patchPre($id, $data);
        }

        try {
            $resource = $this->resource->patch($id, $data);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            return new Rca_Restfull_ApiProblem($code, $e);
        }

        if (!$resource instanceof Rca_Restfull_HalResource) {
            $resource = new Rca_Restfull_HalResource($resource, $id);
        }

        $this->injectSelfLink($resource);

        if (method_exists($this, '_patchPost')) {
            $this->_patchPost($id, $data, $resource);
        }
        return $resource;
    }

    /**
     * Replace an entire resource collection
     *
     * Not marked as abstract, as that would introduce a BC break
     * (introduced in 2.1.0); instead, raises an exception if not implemented.
     *
     * @param  mixed $data
     * @return mixed
     * @throws Zend_Controller_Action_Exception
     */
    public function replaceList($params, $data)
    {
        if (!$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_replaceListPre')) {
            $this->_replaceListPre($params, $data);
        }

        try {
            $collection = $this->service->replaceList($data);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            return new Rca_Restfull_ApiProblem($code, $e);
        }

        if (!$collection instanceof Rca_Restfull_HalCollection) {
            $collection = new Rca_Restfull_HalCollection($collection);
        }
        $this->injectSelfLink($collection);
        $collection->setCollectionRoute($this->route);
        $collection->setResourceRoute($this->route);
        $collection->setPage($this->getRequest()->getQuery('page', 1));
        $collection->setPageSize($this->pageSize);
        $collection->setCollectionName($this->collectionName);

        if (method_exists($this, '_replaceListPost')) {
            $this->_replaceListPost($params, $data, $collection);
        }
        return $collection;
    }

    /**
     * Update an existing resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return mixed
     */
    public function update($id, $data)
    {
        if ($id && !$this->isMethodAllowedForResource()) {
            return $this->createMethodNotAllowedResponse($this->resourceHttpOptions);
        }
        if (!$id && !$this->isMethodAllowedForCollection()) {
            return $this->createMethodNotAllowedResponse($this->collectionHttpOptions);
        }

        if (method_exists($this, '_updatePre')) {
            $this->_updatePre($id, $data);
        }

        try {
            $resource = $this->service->update($id, $data);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            return new Rca_Restfull_ApiProblem($code, $e);
        }

        if (!$resource instanceof Rca_Restfull_HalResource) {
            $resource = new Rca_Restfull_HalResource($resource, $id);
        }

        $this->injectSelfLink($resource);

        if (method_exists($this, '_updatePost')) {
            $this->_updatePost($id, $data, $resource);
        }
        return $resource;
    }

    /**
     * Basic functionality for when a page is not available
     *
     * @return array
     */
    public function notFoundAction()
    {
        $this->getResponse()->setHttpResponseCode(404);

        return array(
            'content' => 'Page not found'
        );
    }

    /**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to skip processing the current action.
     *
     * @return void
     */
    public function preDispatch()
    {
    }

    /**
     * Post-dispatch routines
     *
     * Called after action method execution. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to process an additional action.
     *
     * Common usages for postDispatch() include rendering content in a sitewide
     * template, link url correction, setting headers, etc.
     *
     * @return void
     */
    public function postDispatch()
    {
    }

    /**
     * Proxy for undefined methods.  Default behavior is to throw an
     * exception on undefined methods, however this function can be
     * overridden to implement magic (dynamic) actions, or provide run-time
     * dispatching.
     *
     * @param  string $methodName
     * @param  array $args
     * @return void
     * @throws Zend_Controller_Action_Exception
     */
    public function __call($methodName, $args)
    {
        require_once 'Zend/Controller/Action/Exception.php';
        if ('Action' == substr($methodName, -6)) {
            $action = substr($methodName, 0, strlen($methodName) - 6);
            throw new Zend_Controller_Action_Exception(sprintf('Action "%s" does not exist and was not trapped in __call()', $action), 404);
        }

        throw new Zend_Controller_Action_Exception(sprintf('Method "%s" does not exist and was not trapped in __call()', $methodName), 500);
    }

    /**
     * Dispatch the requested action
     *
     * @param string $action Method name of action
     * @return void
     */
    public function dispatch($action)
    {
        // Notify helpers of action preDispatch state
        $this->_helper->notifyPreDispatch();

        $this->preDispatch();
        $request = $this->getRequest();

        if ($request->isDispatched()) {
            if (null === $this->_classMethods) {
                $this->_classMethods = get_class_methods($this);
            }

            // Was an "action" requested?
            $action  = $this->getParam('action', false);
            //var_dump($action);
            if ($action && $action != 'index') {
                // Handle arbitrary methods, ending in Action
                $method = static::getMethodFromAction($action);
                if (! method_exists($this, $method)) {
                    $method = 'notFoundAction';
                }
                $return = $this->$method();
                return $return;
            }

            // RESTful methods
            $method = strtolower($request->getMethod());
            $response = $this->getResponse();
            switch ($method) {
                // Custom HTTP methods (or custom overrides for standard methods)
                case (isset($this->customHttpMethodsMap[$method])):
                    $callable = $this->customHttpMethodsMap[$method];
                    $action = $method;
                    $return = call_user_func($callable, $e);
                    break;
                // DELETE
                case 'delete':
                    $id = $this->getIdentifier($request);
                    if ($id !== false) {
                        $action = 'delete';
                        $return = $this->delete($id);
                        break;
                    }

                    $action = 'deleteList';
                    $return = $this->deleteList($this->getAllParams());
                    break;
                // GET
                case 'get':
                    $id = $this->getIdentifier($request);
                    if ($id !== false) {
                        $action = 'get';
                        $return = $this->get($id);
                        break;
                    }
                    $action = 'getList';
                    $return = $this->getList($this->getAllParams());
                    break;
                // HEAD
                case 'head':
                    $id = $this->getIdentifier($request);
                    if ($id === false) {
                        $id = null;
                    }
                    $action = 'head';
                    $this->head($id);
                    $response->setBody('');
                    return $response;
                    break;
                // OPTIONS
                case 'options':
                    $action = 'options';
                    $this->options();
                    return $response;
                    break;
                // PATCH
                case 'patch':
                    $id = $this->getIdentifier($request);
                    if ($id === false) {
                        $response->setHttpResponseCode(405);
                        return $response;
                    }
                    $data   = $this->processBodyContent($request);
                    $action = 'patch';
                    $return = $this->patch($id, $data);
                    break;
                // POST
                case 'post':
                    $action = 'create';
                    $return = $this->processPostData($request);
                    break;
                // PUT
                case 'put':
                    $id   = $this->getIdentifier($request);
                    $data = $this->processBodyContent($request);

                    if ($id !== false) {
                        $action = 'update';
                        $return = $this->update($id, $data);
                        break;
                    }

                    $action = 'replaceList';
                    $return = $this->replaceList($data);
                    break;
                // All others...
                default:
                    $response->setHttpResponseCode(405);
                    return $response;
            }

            $this->postDispatch();
        }

        if (!$return instanceof Rca_Restfull_ApiProblem
            && !$return instanceof Rca_Restfull_HalResource
            && !$return instanceof Rca_Restfull_HalCollection
        ) {
            return $return;
        }

        $this->getResponse()->setBody($this->render($return));
        // whats actually important here is that this action controller is
        // shutting down, regardless of dispatching; notify the helpers of this
        // state
        //$this->_helper->notifyPostDispatch();
    }

    /**
     * Call the action specified in the request object, and return a response
     *
     * Not used in the Action Controller implementation, but left for usage in
     * Page Controller implementations. Dispatches a method based on the
     * request.
     *
     * Returns a Zend_Controller_Response_Abstract object, instantiating one
     * prior to execution if none exists in the controller.
     *
     * {@link preDispatch()} is called prior to the action,
     * {@link postDispatch()} is called following it.
     *
     * @param null|Zend_Controller_Request_Abstract $request Optional request
     * object to use
     * @param null|Zend_Controller_Response_Abstract $response Optional response
     * object to use
     * @return Zend_Controller_Response_Abstract
     */
    public function run(Zend_Controller_Request_Abstract $request = null, Zend_Controller_Response_Abstract $response = null)
    {
        if (null !== $request) {
            $this->setRequest($request);
        } else {
            $request = $this->getRequest();
        }

        if (null !== $response) {
            $this->setResponse($response);
        }

        /*$action = $request->getActionName();
        if (empty($action)) {
            $action = 'index';
        }
        $action = $action . 'Action';*/
        $action = '';

        $request->setDispatched(true);
        $this->dispatch($action);


        return $this->getResponse();
    }

    /**
     * Process post data and call create
     *
     * @param Request $request
     * @return mixed
     */
    public function processPostData(Zend_Controller_Request_Abstract $request)
    {
        if ($this->requestHasContentType($request, self::CONTENT_TYPE_JSON)) {
            $data = json_decode($request->getRawBody(), $this->jsonDecodeType);
        } else {
            $data = $request->getPost();
        }

        return $this->create($data);
    }

    /**
     * Retrieve the identifier, if any
     *
     * Attempts to see if an identifier was passed in either the URI or the
     * query string, returning if if found. Otherwise, returns a boolean false.
     *
     * @param  Request $request
     * @return false|mixed
     */
    protected function getIdentifier(Zend_Controller_Request_Abstract $request)
    {
        $id = $this->getParam('id', false);
        if ($id) {
            return $id;
        }

        $id = $request->getQuery('id', false);
        if ($id) {
            return $id;
        }

        return false;
    }

    /**
     * Retrieve an identifier from a resource
     *
     * @param  array|object $resource
     * @return false|int|string
     */
    protected function getIdentifierFromResource($resource)
    {
        // Found id in array
        if (is_array($resource) && array_key_exists('id', $resource)) {
            return $resource['id'];
        }

        // No id in array, or not an object; return false
        if (is_array($resource) || !is_object($resource)) {
            return false;
        }

        // Found public id property on object
        if (isset($resource->id)) {
            return $resource->id;
        }

        // Found public id getter on object
        if (method_exists($resource, 'getid')) {
            return $resource->getId();
        }

        // not found
        return false;
    }

    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        $method  = str_replace(array('.', '-', '_'), ' ', $action);
        $method  = ucwords($method);
        $method  = str_replace(' ', '', $method);
        $method  = lcfirst($method);
        $method .= 'Action';

        return $method;
    }

    /**
     * Is the current HTTP method allowed for a resource?
     *
     * @return bool
     */
    protected function isMethodAllowedForResource()
    {
        return $this->isMethodAllowed($this->resourceHttpOptions);
    }

    /**
     * Is the current HTTP method allowed for the resource (collection)?
     *
     * @return bool
     */
    protected function isMethodAllowedForCollection()
    {
        return $this->isMethodAllowed($this->collectionHttpOptions);
    }

    protected function isMethodAllowed(array $methodsAllowed)
    {
        array_walk($methodsAllowed, function (&$method) {
            $method = strtoupper($method);
        });
        $options = array_merge($methodsAllowed, array('OPTIONS', 'HEAD'));
        $request = $this->getRequest();
        $method  = strtoupper($request->getMethod());
        if (!in_array($method, $options)) {
            return false;
        }
        return true;
    }

    /**
     * Creates a "405 Method Not Allowed" response detailing the available options
     *
     * @param  array $options
     * @return Response
     */
    protected function createMethodNotAllowedResponse(array $options)
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(405);
        $response->setHeader('Allow', implode(', ', $options));
        return $response;
    }

    protected function injectSelfLink(Rca_Restfull_LinkCollectionAwareInterface $resource)
    {
        $self = new Rca_Restfull_Link('self');
        $route = $this->getFrontController()->getRouter()->getCurrentRouteName();
        $self->setRoute($route);
        if ($resource instanceof Rca_Restfull_HalResource) {
            $self->setRouteParams(array('id' => $resource->id));
        }
        $resource->getLinks()->add($self);
    }
}

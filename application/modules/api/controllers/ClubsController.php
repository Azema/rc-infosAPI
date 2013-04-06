<?php

class Api_ClubsController extends Rca_Controller_Action_Restfull
{
    public $route = 'clubs';

    public $resource = 'Model_Club';

    public $collectionName = 'clubs';

    public $pageSize = 5;

    public function init()
    {
        $this->service = new Service_Clubs();
        /* Initialize action controller here */
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
            //$collection = new Zend_Paginator(new Zend_Paginator_Adapter_Array($collection));
        } catch (Exception $e) {
            return new Rca_Restfull_ApiProblem(500, $e);
        }

        if (method_exists($this, '_getListPost')) {
            $collection = $this->_getListPost($params, $collection);
        }

        if (!$collection instanceof Rca_Restfull_HalCollection) {
            $collection = new Rca_Restfull_HalCollection($collection);
        }

        $this->injectSelfLink($collection);
        $collection->setCollectionRoute($this->route);
        $collection->setResourceRoute($this->route);
        $collection->setPage($this->getParam('page', 1));
        $collection->setPageSize($this->pageSize);
        $collection->setCollectionName($this->collectionName);
        $collection->setCollectionRouteOptions(array('page' => 1));
        if (array_key_exists('leagueId', $params)) {
            $collection->setCollectionRoute('league_clubs');
            $collection->setCollectionRouteOptions(
                array_merge($collection->collectionrouteoptions, array('leagueId' => $params['leagueId']))
            );
        }
        return $collection;
    }

    protected function _getPost($id, $resource)
    {
        $leagueId = $resource->resource->leagueId;
        $league = $this->service->getLeague($leagueId);
        $links = $resource->getLinks();

        if (!empty($league)) {
            $link = new Rca_Restfull_Link('league');
            $link->setRouteParams(array('id' => $leagueId));
            $link->setRoute('leagues');
            $links->add($link);
            unset($resource->resource->leagueId);
        }
        $link = new Rca_Restfull_Link('clubs');
        $link->setRoute('clubs', array('id' => ''));
        $links->add($link);
    }

    protected function _getListPost($params, $collection)
    {
        foreach ($collection as $key => $resource) {
            $leagueId = $resource['leagueId'];
            unset($resource['leagueId']);
            $id = $resource['id'];
            $resource = new Rca_Restfull_HalResource($resource, $id);
            $links = $resource->getLinks();
            $selfLink = new Rca_Restfull_Link('self');
            $selfLink->setRoute($this->route, array('id' => $id));
            $links->add($selfLink);
            $link = new Rca_Restfull_Link('league');
            $link->setRoute('leagues', array('id' => $leagueId));
            $links->add($link);
            $collection[$key] = $resource;
        }
        return new Zend_Paginator(new Zend_Paginator_Adapter_Array($collection));
    }
}
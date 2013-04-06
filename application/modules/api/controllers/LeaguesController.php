<?php

class Api_LeaguesController extends Rca_Controller_Action_Restfull
{
    public $route = 'leagues';

    public $resource = 'Model_League';

    public $collectionName = 'leagues';

    public function init()
    {
        $this->service = new Service_Leagues();
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

    protected function _getPost($id, $resource)
    {
        $clubs = $this->service->getClubs($id);
        if (!empty($clubs)) {
            $collection = new Rca_Restfull_HalCollection($clubs);
            $self = new Rca_Restfull_Link('self');
            $self->setRouteParams(array('leagueId' => $resource->id));
            $self->setRoute('league_clubs');
            $collection->getLinks()->add($self);
            $collection->setCollectionName('clubs');
            $collection->setCollectionRoute('clubs');
            $collection->setResourceRoute('clubs');
            $resource->resource->clubs = $collection;
        }
    }
}


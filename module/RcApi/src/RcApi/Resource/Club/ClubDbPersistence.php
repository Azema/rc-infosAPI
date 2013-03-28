<?php

namespace RcApi\Resource\Club;

use PhlyRestfully\Exception\CreationException;
use PhlyRestfully\Exception\UpdateException;
use PhlyRestfully\Exception\PatchException;
use Zend\Db\Exception\ExceptionInterface as DbException;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class ClubDbPersistence implements
    ListenerAggregateInterface,
    ClubPersistenceInterface
{
    /**
     * @var ClassMethodsHydrator
     */
    protected $hydrator;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var TableGateway
     */
    protected $table;

    /**
     * User for whom to manipulate status; none removes ability to
     * create/update/patch/delete, but will retrieve any status by id, or a
     * list of all statuses from all users.
     * @var string
     */
    protected $user;

    /**
     * @var StatusValidator
     */
    protected $validator;

    public function __construct(TableGateway $table, $user = null)
    {
        $this->table = $table;
        $this->user  = $user;
        $this->validator = new ClubValidator();
        $this->hydrator  = new ClassMethodsHydrator();
        $this->hydrator->setUnderscoreSeparatedKeys(false);
    }

    public function attach(EventManagerInterface $events)
    {
        $events->attach('create', array($this, 'onCreate'));
        $events->attach('update', array($this, 'onUpdate'));
        $events->attach('patch', array($this, 'onPatch'));
        $events->attach('delete', array($this, 'onDelete'));
        $events->attach('fetch', array($this, 'onFetch'));
        $events->attach('fetchAll', array($this, 'onFetchAll'));
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function onCreate($e)
    {
        if (false === $data = $e->getParam('data', false)) {
            throw new CreationException('Missing data');
        }

        $data = (array) $data;
        $club = new Club();
        $club = $this->hydrator->hydrate($data, $club);
        if (!$this->validator->isValid($club)) {
            throw new CreationException('Club failed validation');
        }
        $data = $this->hydrator->extract($club);
        var_dump($data); 
        try {
            $this->table->insert($data);
        } catch (DbException $exception) {
            throw new CreationException('DB exception when creating club', null, $exception);
        }

        return $data;
    }

    public function onUpdate($e)
    {
        if (false === $id = $e->getParam('id', false)) {
            throw new UpdateException('Missing id');
        }

        if (false === $data = $e->getParam('data', false)) {
            throw new UpdateException('Missing data');
        }

        $data     = (array) $data;
        $rowset   = $this->table->select(array('clb_id' => $id));
        $original = $rowset->current();
        if (!$original) {
            throw new UpdateException('Cannot update; club not found', 404);
        }

        $updated = $this->hydrator->hydrate($data, new Club());
        $updated->setId($original->getId());

        if (!$this->validator->isValid($updated)) {
            throw new UpdateException('Updated club failed validation');
        }

        $data = $this->hydrator->extract($updated);
        try {
            $this->table->update($data, array('clb_id' => $id));
        } catch (DbException $exception) {
            throw new UpdateException('DB exception when updating club', null, $exception);
        }

        return $updated;
    }

    public function onPatch($e)
    {
        if (false === $id = $e->getParam('id', false)) {
            throw new PatchException('Missing id');
        }

        if (false === $data = $e->getParam('data', false)) {
            throw new PatchException('Missing data');
        }

        $data     = (array) $data;
        $rowset   = $this->table->select(array('clb_id' => $id));
        $original = $rowset->current();
        if (!$original) {
            throw new PatchException('Cannot patch; club not found', 404);
        }

        //$allowedUpdates = array(
            //'type'       => true,
            //'text'       => true,
            //'image_url'  => true,
            //'link_url'   => true,
            //'link_title' => true,
        //);
        //$updates = array_intersect_key($data, $allowedUpdates);

        $club = $this->hydrator->hydrate($data, $original);
        if (!$this->validator->isValid($club)) {
            throw new PatchException('Patched club failed validation');
        }

        try {
            $this->table->update($data, array('clb_id' => $id));
        } catch (DbException $exception) {
            throw new PatchException('DB exception when updating club', null, $exception);
        }

        return $club;
    }

    public function onDelete($e)
    {
        if (false === $id = $e->getParam('id', false)) {
            return false;
        }

        if (!$this->table->delete(array('clb_id' => $id))) {
            return false;
        }

        return true;
    }

    public function onFetch($e)
    {
        if (false === $id = $e->getParam('id', false)) {
            return false;
        }

        $criteria = array('clb_id' => $id);
        $rowset = $this->table->select($criteria);
        $item   = $rowset->current();
        if (!$item) {
            return false;
        }
        return $item;
    }

    public function onFetchAll($e)
    {
        return $this->table->fetchAll();
    }
}

<?php

namespace RcApi\Resource\Club;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Paginator\Adapter\DbSelect as DbTablePaginator;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use RcApi\Hydrator;

class ClubDbTable extends AbstractTableGateway
{
    protected $prefix = 'clb_';

    public function __construct(Adapter $adapter, $table = 'clubs')
    {
        $this->adapter            = $adapter;
        $this->table              = $table;
        $rowPrototype             = new Club();
        $hydratorPrototype        = new Hydrator\ClubHydrator();
        $hydratorPrototype->setPrefix($this->prefix);
        $this->resultSetPrototype = new HydratingResultSet($hydratorPrototype, $rowPrototype);
        $this->resultSetPrototype->buffer();
        $this->initialize();
    }

    public function fetchAll()
    {
        $select = $this->getSql()->select();
        $select->order('clb_updatedAt DESC');

        $adapter = new DbTablePaginator(
            $select,
            $this->getAdapter(),
            $this->resultSetPrototype
        );
        $paginator = new Paginator($adapter);
        return $paginator;
    }

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set)
    {
        if (!$this->isInitialized) {
            $this->initialize();
        }
        $insert = $this->sql->insert();
        $data = array();
        var_dump($set);
        foreach ($set as $key => $value) {
            if ($key == 'leagueId') {
                $key = 'leg_id';
            } elseif ($key == 'tracks') {
                continue;
            }
            $data[$this->prefix.$key] = $value;
        }
        $data[$this->prefix.'createdAt'] = date('c');
        $insert->values($data);
        return $this->executeInsert($insert);
    }
}

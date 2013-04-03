<?php

class Model_DbTable_Leagues extends Zend_Db_Table_Abstract
{
    protected $_name = 'leagues';

    protected $_primary = array('id');

    protected $_rowClass = 'Model_League';
}


<?php

class Model_DbTable_Tracks extends Zend_Db_Table_Abstract
{
    protected $_name = 'tracks';

    protected $_primary = array('id');

    protected $_rowClass = 'Model_Track';
}


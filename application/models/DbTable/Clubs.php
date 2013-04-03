<?php

class Model_DbTable_Clubs extends Zend_Db_Table_Abstract
{
    protected $_name = 'clubs';

    protected $_primary = array('id');

    protected $_rowClass = 'Model_Club';
}


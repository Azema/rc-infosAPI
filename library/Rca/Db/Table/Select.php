<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

namespace Rca\Db\Table;

/**
 * @see \Zend_Db_Table_Select
 */
require_once 'Zend/Db/Table/Select.php';

/**
 * Classe abstraite des classes de tables de BDD
 *
 * @category  Rca
 * @package   Db
 * @author    Manuel Hervo <manuel.hervo@gmail.com>
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */
class Select extends \Zend_Db_Table_Select
{

    /**
     * Adds a FROM table and optional columns to the query.
     *
     * The table name can be expressed
     *
     * @param  array|string|Zend_Db_Expr|Zend_Db_Table_Abstract $name The table name or an
                                                                      associative array relating
                                                                      table name to correlation
                                                                      name.
     * @param  array|string|Zend_Db_Expr $cols The columns to select from this table.
     * @param  string $schema The schema name to specify, if any.
     * @return Zend_Db_Table_Select This Zend_Db_Table_Select object.
     */
    public function from($name, $cols = self::SQL_WILDCARD, $schema = null)
    {
        if ($name instanceof \Zend_Db_Table_Abstract) {
            $info = $name->info();
            $name = $info[\Zend_Db_Table_Abstract::NAME];
            if (isset($info[\Zend_Db_Table_Abstract::SCHEMA])) {
                $schema = $info[\Zend_Db_Table_Abstract::SCHEMA];
            }
        }
    	if ($cols == self::SQL_WILDCARD) {
    		$cols = $this->getTable()->getMapSelectColumns();
    	}
        

        return $this->joinInner($name, null, $cols, $schema);
    }

    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param int      $type  OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function where($cond, $value = null, $type = null)
    {
        $cond = $this->_getCond($cond);
        $this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, true);

        return $this;
    }

    protected function _getCond($cond)
    {
        $columns = array_flip($this->getTable()->getMap());
        $pos = strpos($cond, ' ');
        $newKey = substr($cond, 0, $pos);
        $rest = substr($cond, $pos);
        if (array_key_exists($newKey, $columns)) {
            return $columns[$newKey] . $rest;
        }
        return $cond;
    }

    /**
     * Adds a WHERE condition to the query by OR.
     *
     * Otherwise identical to where().
     *
     * @param string   $cond  The WHERE condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param int      $type  OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     *
     * @see where()
     */
    public function orWhere($cond, $value = null, $type = null)
    {
        $cond = $this->_getCond($cond);
        $this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, false);

        return $this;
    }
}
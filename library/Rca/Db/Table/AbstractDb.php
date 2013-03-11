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
 * @see \Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';
require_once 'Zend/Db/Table/Row.php';
require_once 'Zend/Db/Table/Rowset.php';
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
abstract class AbstractDb extends \Zend_Db_Table_Abstract
{
    /**
     * Classname for row
     *
     * @var string
     */
    protected $_rowClass = '\Zend_Db_Table_Row';

    /**
     * Classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = '\Zend_Db_Table_Rowset';

    /**
     * Table de mapping entre les propriétés et les colonnes
     *
     * @var array
     */
    protected $_map = array();

    /**
     * Table de mapping pour modifier le nom des colonnes
     *
     * @var array
     */
    protected $_customMap = array();

    /**
     * Returns an instance of a Zend_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
        $select = new Select($this);
        if ($withFromPart == self::SELECT_WITH_FROM_PART) {
            $select->from($this->info(self::NAME), $this->getMapSelectColumns(), $this->info(self::SCHEMA));
        }
        return $select;
    }

    /**
     * Fetches rows by primary key.  The argument specifies one or more primary
     * key value(s).  To find multiple rows by primary key, the argument must
     * be an array.
     *
     * This method accepts a variable number of arguments.  If the table has a
     * multi-column primary key, the number of arguments must be the same as
     * the number of columns in the primary key.  To find multiple rows in a
     * table with a multi-column primary key, each argument must be an array
     * with the same number of elements.
     *
     * The find() method always returns a Rowset object, even if only one row
     * was found.
     *
     * @param  mixed $key The value(s) of the primary keys.
     * @return Zend_Db_Table_Rowset_Abstract Row(s) matching the criteria.
     * @throws Zend_Db_Table_Exception
     */
    public function find()
    {
        $this->_setupPrimaryKey();
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);

        if (count($args) < count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new \Zend_Db_Table_Exception("Too few columns for the primary key");
        }

        if (count($args) > count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new \Zend_Db_Table_Exception("Too many columns for the primary key");
        }

        $whereList = array();
        $numberTerms = 0;
        foreach ($args as $keyPosition => $keyValues) {
            $keyValuesCount = count($keyValues);
            // Coerce the values to an array.
            // Don't simply typecast to array, because the values
            // might be Zend_Db_Expr objects.
            if (!is_array($keyValues)) {
                $keyValues = array($keyValues);
            }
            if ($numberTerms == 0) {
                $numberTerms = $keyValuesCount;
            } else if ($keyValuesCount != $numberTerms) {
                require_once 'Zend/Db/Table/Exception.php';
                throw new \Zend_Db_Table_Exception("Missing value(s) for the primary key");
            }
            $keyValues = array_values($keyValues);
            for ($i = 0; $i < $keyValuesCount; ++$i) {
                if (!isset($whereList[$i])) {
                    $whereList[$i] = array();
                }
                $whereList[$i][$keyPosition] = $keyValues[$i];
            }
        }

        $whereClause = null;
        if (count($whereList)) {
            $whereOrTerms = array();
            $tableName = $this->_db->quoteTableAs($this->_name, null, true);
            foreach ($whereList as $keyValueSets) {
                $whereAndTerms = array();
                foreach ($keyValueSets as $keyPosition => $keyValue) {
                    $type = $this->_metadata[$keyNames[$keyPosition]]['DATA_TYPE'];
                    $columnName = $this->_db->quoteIdentifier($keyNames[$keyPosition], true);
                    $whereAndTerms[] = $this->_db->quoteInto(
                        $tableName . '.' . $columnName . ' = ?',
                        $keyValue, $type);
                }
                $whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
            }
            $whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
        }

        // issue ZF-5775 (empty where clause should return empty rowset)
        if ($whereClause == null) {
            $rowsetClass = $this->getRowsetClass();
            if (!class_exists($rowsetClass)) {
                require_once 'Zend/Loader.php';
                \Zend_Loader::loadClass($rowsetClass);
            }
            return new $rowsetClass(array('table' => $this, 'rowClass' => $this->getRowClass(), 'stored' => true));
        }

        return $this->fetchAll($whereClause);
    }

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if (!($where instanceof \Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            if ($count !== null || $offset !== null) {
                $select->limit($count, $offset);
            }

        } else {
            $select = $where;
        }

        $rows = $this->_fetch($select);

        $data  = array(
            'table'    => $this,
            'data'     => $rows,
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();
        return new $rowsetClass($data);
    }

    /**
     * Fetches one row in an object of type Zend_Db_Table_Row_Abstract,
     * or returns null if no row matches the specified criteria.
     *
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $offset OPTIONAL An SQL OFFSET value.
     * @return Zend_Db_Table_Row_Abstract|null The row results per the
     *     Zend_Db_Adapter fetch mode, or null if no row found.
     */
    public function fetchRow($where = null, $order = null, $offset = null)
    {
        if (!($where instanceof \Zend_Db_Table_Select)) {
            $select = $this->select();

            if ($where !== null) {
                $this->_where($select, $where);
            }

            if ($order !== null) {
                $this->_order($select, $order);
            }

            $select->limit(1, ((is_numeric($offset)) ? (int) $offset : null));

        } else {
            $select = $where->limit(1, $where->getPart(\Zend_Db_Select::LIMIT_OFFSET));
        }

        $rows = $this->_fetch($select);

        if (count($rows) == 0) {
            return false;
        }

        $data = array(
            'table'    => $this,
            'data'     => $rows[0],
            'readOnly' => $select->isReadOnly(),
            'stored'   => true
        );

        $rowClass = $this->getRowClass();
        return new $rowClass($data);
    }

    /**
     * Fetches a new blank row (not from the database).
     *
     * @param  array $data OPTIONAL data to populate in the new row.
     * @param  string $defaultSource OPTIONAL flag to force default values into new row
     * @return Zend_Db_Table_Row_Abstract
     */
    public function createRow(array $data = array(), $defaultSource = null)
    {
        $cols     = $this->_getCols();
        $defaults = array_combine($cols, array_fill(0, count($cols), null));

        // nothing provided at call-time, take the class value
        if ($defaultSource == null) {
            $defaultSource = $this->_defaultSource;
        }

        if (!in_array($defaultSource, array(self::DEFAULT_CLASS, self::DEFAULT_DB, self::DEFAULT_NONE))) {
            $defaultSource = self::DEFAULT_NONE;
        }

        if ($defaultSource == self::DEFAULT_DB) {
            foreach ($this->_metadata as $metadataName => $metadata) {
                if (($metadata['DEFAULT'] != null) &&
                    ($metadata['NULLABLE'] !== true || ($metadata['NULLABLE'] === true && isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === true)) &&
                    (!(isset($this->_defaultValues[$metadataName]) && $this->_defaultValues[$metadataName] === false))) {
                    $defaults[$metadataName] = $metadata['DEFAULT'];
                }
            }
        } elseif ($defaultSource == self::DEFAULT_CLASS && $this->_defaultValues) {
            foreach ($this->_defaultValues as $defaultName => $defaultValue) {
                if (array_key_exists($defaultName, $defaults)) {
                    $defaults[$defaultName] = $defaultValue;
                }
            }
        }

        $config = array(
            'table'    => $this,
            'data'     => $defaults,
            'readOnly' => false,
            'stored'   => false
        );

        $rowClass = $this->getRowClass();
        $row = new $rowClass($config);
        $row->setFromArray($data);
        return $row;
    }

    /**
     * Retourne la table de mapping
     *
     * @return array
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * Définit le nom des colonnes à récuperer pour remplir l'objet modèle
     * pour les requêtes SQL
     *
     * @param array $columns Les colonnes souhaitées
     *
     * @return string
     */
    public function getMapSelectColumns($columns = array())
    {
        if (empty($columns)) {
            $columns = array_values($this->_map);
        } else {
            $columns = array_intersect($this->_map, $columns);
        }
        $selectColumns = array();
        foreach ($columns as $name) {
            $colName = array_search($name, $this->_map);
            $selectColumns[] = $colName . ' AS ' . $name;
        }
        return $selectColumns;
    }

    /**
     * Generate WHERE clause from user-supplied string or array
     *
     * @param  string|array $where  OPTIONAL An SQL WHERE clause.
     * @return Zend_Db_Table_Select
     */
    protected function _where(\Zend_Db_Table_Select $select, $where)
    {
        $where = (array)$where;

        foreach ($where as $key => $val) {
            // is $key an int?
            if (is_int($key)) {
                // $val is the full condition
                $select->where($val);
            } else {
                // $key is the condition with placeholder,
                // and $val is quoted into the condition
                $select->where($key, $val);
            }
        }

        return $select;
    }

    /**
     * Initialise les metadata, la table de mapping des colonnes
     * et les clefs primaires.
     *
     * @return boolean
     * @throws Zend_Db_Table_Exception
     */
    protected function _setupMetadata()
    {
    	// Assume that metadata will be loaded from cache
        $isMetadataFromCache = parent::_setupMetadata();

        $first = reset($this->_metadata);
        $prefix = substr($first['COLUMN_NAME'], 0, strpos($first['COLUMN_NAME'], '_')+1);
        foreach ($this->_metadata as $column) {
            $this->_map[$column['COLUMN_NAME']] = str_replace($prefix, '', $column['COLUMN_NAME']);
        }
        $this->_customMap();
        return $isMetadataFromCache;
    }

    protected function _customMap()
    {
    	if (!empty($this->_customMap)) {
    		$this->_map = array_merge($this->_map, $this->_customMap);
    	}
    }

    /**
     * Support method for fetching rows.
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @return array An array containing the row results in FETCH_ASSOC mode.
     */
    protected function _fetch(\Zend_Db_Table_Select $select)
    {
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll(\Zend_Db::FETCH_ASSOC);
        return $data;
    }
}
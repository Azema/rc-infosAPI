<?php

/**
 * Abstract.php
 *
 * LICENCE
 *
 * L'ensemble de ce code relève de la législation française et internationale
 * sur le droit d'auteur et la propriété intellectuelle. Tous les droits de
 * reproduction sont réservés, y compris pour les documents téléchargeables et
 * les représentations iconographiques et photographiques. La reproduction de
 * tout ou partie de ce code sur quelque support que ce soit est formellement
 * interdite sauf autorisation écrite émanant de la société DIGITALEO.
 *
 * PHP version 5.2
 *
 * @category Deo
 * @package  Dao
 * @author   Digitaleo
 * @license  http://www.digitaleo.net/licence.txt Digitaleo Licence
 * @link     http://www.digitaleo.net
 */

namespace Rca\Db;

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * classe abstraite faisant lee mapping entre les objets et la bdd
 *
 * @category Deo
 * @package  Dao
 * @author   Digitaleo
 * @license  http://www.digitaleo.net/licence.txt Digitaleo Licence
 * @link     http://www.digitaleo.net
 */
abstract class AbstractDb
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    CONST FILTER_LOGICAL_LABEL = 'logical';
    CONST FILTER_FIELD_LABEL = 'field';
    CONST FILTER_OPERATOR_LABEL = 'operator';
    CONST FILTER_VALUE_LABEL = 'value';
    CONST OPERATOR_SUPERIOR = '>';
    CONST OPERATOR_SUPERIOREQUALS = '>=';
    CONST OPERATOR_EQUALS = '=';
    CONST OPERATOR_NOTEQUALS = '<>';
    CONST OPERATOR_INFERIOREQUALS = '<=';
    CONST OPERATOR_INFERIOR = '<';
    CONST OPERATOR_STARTSWITH = 'STARTSWITH';
    CONST OPERATOR_CONTAINS = 'CONTAINS';
    CONST OPERATOR_NOTCONTAINS = 'NOTCONTAINS';
    CONST OPERATOR_EMPTY = 'EMPTY';
    CONST OPERATOR_NOTEMPTY = 'NOT EMPTY';
    CONST LOGICAL_OR = 'OR';
    CONST LOGICAL_AND = 'AND';

    protected static $_adapter;

    /**
     * Adapter de connexion a la BDD
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Liste des opérateurs disponible pour le where de la requête
     *
     * @var array
     */
    protected $_operators = array(
        '<',
        '=',
        '>',
    );

    /**
     * nom de la table
     *
     * @var array
     */
    protected $_namz = null;

    /**
     * mappe des colonnes de la table et les champs des objets
     *
     * @var array
     */
    protected $_map = null;

    /**
     * mappe des colonnes de la table et des champs des object
     * qui ne seront pas chargés par défaut dans le read
     *
     * @var array
     */
    protected $_mapOptionnal = array();

    /**
     * mappe des colonnes de la table et des champs des object
     * qui ne seront pas chargés par défaut dans le read
     *
     * @var array
     */
    protected $_mapTools = array(
        'tools_count' => 'COUNT(*)'
    );

    /**
     * mapper les colonnes venant d'une autre table avec leur table respective
     *
     * @var array
     *
     * Example 2:
     * join manage into specificfilter method
     * array (
     *      'ptf_name' => 'parent_testf',
     * );
     *
     * Example 2:
     * join manage into wrapper
     * array (
     *      'ptf_id' => array(
     *           'parent_testf' => array('child_testf.ctf_ptf_id = parent_testf.ptf_id', 'left', true, null),
     *      ),
     * );
     *
     * 0: string    condition on join, manadatory
     * 1: string    method (inner/left), default inner
     * 2: boolean  is table of column, default true
     * 3: array     specific properties, default null
     *
     *
     */
    protected $_join = array();

    /**
     * Le nom de la/les clef primaire de la table
     *
     * @var mixed
     */
    protected $_primary = null;

    /**
     * Constructor
     *
     * @return void
     * @throws Exception
     */
    public function __construct()
    {
        if (isset(self::$_adapter)) {
            $this->_db = self::$_adapter;
        }
        // Extensions...
        $this->init();
        $this->_initPrimaryKey();
    }

    /**
     * extension du contructeur
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * _initPrimaryKey
     *
     * @return void
     */
    private function _initPrimaryKey()
    {
        if (!is_null($this->_primary) && (!is_array($this->_primary))) {
            $this->_primary = array($this->_primary);
        }
    }

    /**
     * Definit l'adapter par defaut
     *
     * @param \Zend_Db_Adapter_Abstract $adapter
     *
     * @return void
     */
    public static function setDefaultAdapter($adapter)
    {
        self::$_adapter = $adapter;
    }

    /**
     * Retourne l'adapter par defaut
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public static function getDefaultAdapter()
    {
        return self::$_adapter;
    }

    /**
     * Retourne l'objet DB
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        return $this->_db;
    }

    /**
     * Definit l'adapter de connexion à la BDD
     *
     * @param \Zend_Db_Adapter_Abstract $adapter
     *
     * @return \Rca\Db\AbstractDb
     */
    public function setDb($adapter)
    {
        $this->_db = $adapter;
        return $this;
    }

    /**
     * Permet de démarrer une transaction
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->getDb()->beginTransaction();
    }

    /**
     * Permet de valider la transaction
     *
     * @return void
     */
    public function commit()
    {
        $this->getDb()->commit();
    }

    /**
     * Permet d'annuler la transaction
     *
     * @return void
     */
    public function rollBack()
    {
        $this->getDb()->rollBack();
    }

    /**
     * Retourne la  liste des tables sur lesquels faire une jointure en fonction des properties ou des filtres
     *
     * @param \Deo\Dao\IFilter $filter Le filtre
     *
     * @return array
     */
    protected function getTableToJoin($properties=array(), $group=array())
    {
        $tablesToJoin = $colsToJoin = array();
        if (count($this->_join)) {
            $mapCols = $this->getMapCols($properties);

            //ajout des colonnes associées au group by
            $group = $this->_getGroup($group);
            if (isset($group) && is_array($group) && count($group) >= 1) {
                $mapCols = array_unique(array_merge($this->getMapCols($group), $mapCols));
            }

            $mapObj = $this->getMapObject($filter);
            //pour chaque table dans _join
            foreach ($this->_join as $col => $cond) {
                //récupération de la liste des colonnes liés à une jointure
                $joinMapCols = array_keys($this->_join, $cond);

                //si une des colonnes dans join map une des colonnes du select
                if (count(array_intersect($joinMapCols, $mapCols)) != 0) {
                    $colsToJoin[$col] = $cond;
                } else {
                    //si une des colonnes dans join est initialisé dans l'objet filtre
                    foreach ($mapObj as $cols => $value) {
                        if (is_null($value)) {
                            continue;
                        }

                        if (in_array($cols, $joinMapCols)) {
                            $colsToJoin[$col] = $cond;
                            break;
                        }
                    }
                }
            }

            $first = reset($colsToJoin);
            if (!is_array($first)) {
                $tablesToJoin = array_values(array_unique($colsToJoin));
            } else {
                foreach ($colsToJoin as $col => $joins) {
                    foreach ($joins as $table => $cond) {
                        $tablesToJoin[$table] = $cond;
                    }
                }
            }
        }

        return $tablesToJoin;
    }

    /**
     * Get joins information in terms of filters
     *
     * @param \Deo\Dao\IFilter $filter
     *
     * @return array joins list
     */
    private function _getJoins($properties=array(), $group=array())
    {
        $joins = array();
        if (count($this->_join)) {
            $first = reset($this->_join);
            if (!is_array($first)) {
                return $joins;
            }
        }

        $joins = $this->getTableToJoin($properties, $group);
        return $joins;
    }

    /**
     * Returns the map depending on the given properties (field properties in the filter)
     * If properties is null, returns 'map' without the 'mapOptional'
     *
     * @param array $properties Properties set into property field
     *
     * @return array
     */
    protected function mapProperties($properties = null)
    {
        $propertiesOptionnal =
            is_array($properties) ? array_intersect_key($this->_mapOptionnal, array_fill_keys($properties, 0)) : array();
        $propertiesTools =
            is_array($properties) ? array_intersect_key($this->_mapTools, array_fill_keys($properties, 0)) : array();
        $properties = is_array($properties) ? array_intersect_key($this->_map, array_fill_keys($properties, 0)) : $this->_map;
        $properties += $propertiesOptionnal + $propertiesTools;

        return $properties;
    }

    /**
     * Retourne la  liste des colonnes / propriétés.
     * Si $properties est vide, toutes les propriétés sont à retourner
     *
     * @param array   $properties   Liste de propriétés
     * @param boolean $addTableName Flag permettant d'ajouter la table dans les jointures
     *
     * @return array
     */
    protected function getMapCols($properties = null, $addTableName = false)
    {
        $properties = $this->mapProperties($properties);
        
        if (empty($properties)) {
            throw new \InvalidArgumentException('properties are not valid');
        }
        /**
         * ajoute le nom de la table associé devant la propriété si celle ci est présente dans _join
         */
        if ($addTableName) {
            foreach ($properties as $key => $col) {
                $properties[$key] = $this->_getColWithTableName($col);
            }
        }

        return $properties;
    }

    /**
     * Get Col Name with Table Name in terms of _join
     * [TABLENAME].[COLNAME]
     *
     * @param string $col Col Name
     *
     * @return string Column name with table Name
     */
    private function _getColWithTableName($col)
    {
        $table = null;

        $join = array_key_exists($col, $this->_join) ? $this->_join[$col] : null;
        if (!preg_match('/\(.*\)/', $col)) {
            if (isset($join)) {
                if (is_array($join)) {
                    foreach ($join as $table => $joinproperties) {
                        if (!isset($joinproperties[2]) || $joinproperties[2] == true) {
                            $table = $table;
                            break;
                        }
                    }
                } else {
                    $table = $join;
                }
            } else {
                $table = $this->_tableName;
            }
        }

        return isset($table) ? $table . '.' . $col : $col;
    }

    /**
     * map column(s) and direction to order by
     *
     * @param array $orders Ordres de tri
     *
     * @return array
     */
    protected function getMapOrder($orders)
    {
        $return = array();
        foreach ($orders as $order) {
            // @TODO: La RegExp ne parait pas etre correcte. En voici une qui irait mieux :
            // /(\w+)\s*(' . Dao\IFilter::ASC . '|' . Dao\IFilter::DESC . ')?$/i
            // \1 : récupère le nom de la colonne
            // \2 : récupère l'ordre si il est spécifié (ASC si omis)
            if (preg_match('/(.*\W)(' . Dao\IFilter::ASC . '|' . Dao\IFilter::DESC . ')\b/si', $order, $matches)) {
                $properties = trim($matches[1]);
                if (array_key_exists($properties, $this->_map)) {
                    $val = $this->_map[$properties];
                    $direction = $matches[2];
                    $return[] = $val . ' ' . $direction;
                }
            }
        }

        return $return;
    }

    /**
     * Returns all the properties available for this object
     * Retourne toutes les propriétés disponible pour cet objet
     *
     * @return array
     */
    private function _getMap()
    {
        return array_merge($this->_map, $this->_mapOptionnal);
    }

    /**
     * Retourne l'operation sur la colonne
     *
     * @param string $col      Nom de la colonne ex: 'colonne1'
     * @param string $operator Operation à effectuer sur la colonne ex: '>'
     * @param mixed  $value    Valeur de l'opération ex: 10
     *
     * @return string ex: 'colonne1 > 10'
     */
    private function getWhereOperation($col, $operator, $value = null)
    {
        $where = null;

        $col = $this->_getColWithTableName($col);

        switch (strtoupper($operator)) {
            case self::OPERATOR_EQUALS:
                // transformation valeur du filtre si tableau avec une seule valeur en valeur pour éviter le IN
                // ex: array(1) => 1
                // ex: array('toto') => 'toto'
                if (is_array($value) && count($value) == 1) {
                    $value = current($value);
                }

                if (is_array($value)) {
                    // On vérifie si le tableau est vide
                    if (empty($value)) {
                        break;
                    }
                    //filtre sur un tableau de valeur (IN): exemple array('1','2','3'); $in = $col . ' IN (' . implode(',', $value) . ')';
                    $firstElement = reset($value);
                    if (is_string($firstElement) && !empty($firstElement)) {
                        $where = $col . ' IN (' . $this->getDb()->quote($value) . ')';
                    } elseif (!is_string($firstElement)) {
                        $where = $col . ' IN (' . implode(',', $value) . ')';
                    }
                    break;
                } elseif (preg_match('/[' . implode('', $this->_operators) . ']/', $value)) {
                    $where = $col . $value;
                    break;
                }
            case self::OPERATOR_SUPERIOR:
            case self::OPERATOR_SUPERIOREQUALS:
            case self::OPERATOR_INFERIOREQUALS:
            case self::OPERATOR_INFERIOR:
            case self::OPERATOR_NOTEQUALS:
                $where = $col . ' ' . $operator . ' ' . $this->getDb()->quote($value);
                break;
            case self::OPERATOR_EMPTY:
                $where = $col . ' IS NULL';
                break;
            case self::OPERATOR_NOTEMPTY:
                $where = $col . ' IS NOT NULL';
                break;
            case self::OPERATOR_CONTAINS:
                $where = $col
                    . " LIKE "
                    . $this->getDb()->quote('%' . $value . '%');
                break;
            case self::OPERATOR_STARTSWITH:
                $where = $col
                    . " LIKE "
                    . $this->getDb()->quote($value . '%');
                break;
            case self::OPERATOR_NOTCONTAINS:
                $where = $col
                    . " NOT LIKE "
                    . $this->getDb()->quote('%' . $value . '%');
                break;
            default:
                $this->getResources()->err('Invalid oprator=' . $operator);
                break;
        }

        return $where;
    }

    /**
     * get Complex filter
     *
     * @param array $complex
     *
     * @return string
     */
    protected function getWhereComplex($complex)
    {
        $logical = array_key_exists(self::FILTER_LOGICAL_LABEL, $complex) ? $complex[self::FILTER_LOGICAL_LABEL] : self::LOGICAL_AND;
        unset($complex[self::FILTER_LOGICAL_LABEL]);

        $where = array();
        foreach ($complex as $filter) {
            $col = $this->_map[$filter[self::FILTER_FIELD_LABEL]];
            if (empty($col)) {
                continue;
            }
            $operator = $filter[self::FILTER_OPERATOR_LABEL];
            $value = array_key_exists(self::FILTER_VALUE_LABEL, $filter) ? $filter[self::FILTER_VALUE_LABEL] : null;
            $where[] = $this->getWhereOperation($col, $operator, $value);
        }

        if (count($where) == 0) {
            return;
        }

        return '( ' . implode(' ' . $logical . ' ', $where) . ' )';
    }

    /**
     * Retourne le tableau de condition à partir du filtre en entrée
     *
     * @param Object|\Deo\Dao\IFilter $filter filtres
     *
     * @return array
     */
    protected function getWhere($whereClauses)
    {
        $where = array();
        $maps = $this->_getMap();

        // Gestion des filtres
        foreach ($maps as $property => $col) {
            if (!array_key_exists($property, $whereClauses)) {
                continue;
            }
            $clauseWhere = $whereClauses[$property];

            $complex = false;
            if (is_array($clauseWhere)) {
                $firstElement = reset($clauseWhere);
                $complex = is_array($firstElement);
            }
            if ($complex) {
                //filtre complexe sur une propriété
                foreach ($clauseWhere as $filterComplex) {
                    $operator = $filterComplex[self::FILTER_OPERATOR_LABEL];
                    $value = array_key_exists(self::FILTER_VALUE_LABEL, $filterComplex) ? $filterComplex[self::FILTER_VALUE_LABEL] : null;
                    $result = $this->getWhereOperation($col, $operator, $value);
                    if (null !== $result) {
                        $where[] = $result;
                    }
                }
            } else {
                //filtre simple
                switch ((string)$clauseWhere) {
                    case Dao\IFilter::OPERATOR_EMPTY:
                    case Dao\IFilter::OPERATOR_NOTEMPTY:
                        $result = $this->getWhereOperation($col, $filter->$property);
                        break;
                    default:
                        $result = $this->getWhereOperation($col, Dao\IFilter::OPERATOR_EQUALS, $filter->$property);
                        break;
                }
                if (null !== $result) {
                    $where[] = $result;
                }
            }
        }

        return $where;
    }

    public function fetchAll($properties = array(), $where = array(), $limit = null, $offset = 0, 
        $sort = array(), $group = array())
    {
        $select = $this->_selectFromFilter($properties, $where, $limit, $offset, $sort, $group);
        $stmt = $select->query();
        $data = array();
        while ($obj = $stmt->fetchObject($this->_objectName)) {
            $data[] = $obj;
        }
        return $data;
    }

    public function fetchOne($properties = array(), $where = array())
    {
        $select = $this->_selectFromFilter($properties, $where, 1);
        $stmt = $select->query();
        return $stmt->fetchObject($this->_objectName);
    }

    /**
     * Retourne le nombre de lignes concerné par la requête de lecture
     * liée aux données du filtre en entrée
     *
     * @param \Deo\Dao\IFilter $filters Le filtre
     *
     * @return int
     */
    public function count($properties = array(), $where = array(), $group = array())
    {
        if (!empty($this->_primary)) {
            $properties = $this->_primary;
        }

        $select = $this->_selectFromFilter($properties, $where, null, 0, array(), $group);
        $sql = $select->__toString();

        if (strstr($sql, 'GROUP ') !== FALSE) {
            $stmt = $this->getDb()->query($sql);
            $result = count($stmt->fetchAll(\Zend_Db::FETCH_COLUMN));
        } else {
            $properties = array('tools_count');
            $select = $this->_selectFromFilter($properties, $where, null, 0, array(), $group);
            $result = intval($this->getDb()->fetchOne($select));
        }

        return $result;
    }

    /**
     * Méthode à surcharger dans les classes filles pour appliquer des filtres
     * spécifiques en fonction du modèle
     *
     * @param \Zend_Db_Select  $select Objet Select de Zend_Db
     * @param \Deo\Dao\IFilter $filter Filtre
     *
     * @return void
     */
    protected function _specificFilters($select, $properties=array(), $where=array(), $limit=null, 
        $offset=0, $sort=array(), $group=array())
    {
    }

    /**
     * Retourne les données avec les colonnes associés aux proriétés dans le map
     *
     * @param array $datas liste des champs des objets et leurs valeurs associés
     *
     * @return array liste des colonnes et leurs valeurs associés
     */
    protected function getMapDatas($datas)
    {
        $bind = array();

        //récupération des champs dans datas présents dans map
        $mapping = $this->_getMap();
        $datas = array_intersect_key($datas, $mapping);

        foreach ($datas as $property => $value) {
            $bind[$mapping[$property]] = $value;
        }

        return $bind;
    }

    /**
     * Retire les colonnes présente dans _join
     *
     * @param array $datas Les données
     *
     * @return array
     */
    protected function getMapDatasNotIntoJoin($datas)
    {
        //récupération des champs dans datas présents dans map
        return array_diff_key($this->getMapDatas($datas), $this->_join);
    }

    /**
     * Retourne les données avec les colonnes associés aux proriétés dans le map
     *
     * @param stdClass $object object avec ces valeurs
     *
     * @return array liste des colonnes et leurs valeurs associés
     */
    protected function getMapObject($object)
    {
        return $this->getMapDatas(get_object_vars($object));
    }

    /**
     * Permet de mettre à jour la ou les propriétés de n messages avec la même valeur
     *
     * @param array            $datas   Liste des champs et leurs valeurs associés
     * @param \Deo\Dao\IFilter $filters Le filtre
     *
     * @return int nombre de messages mis à jour
     *
     * @throws Exception
     */
    protected function update($datas, Dao\IFilter $filters = null)
    {
        $bind = $this->getMapDatasNotIntoJoin($datas);

        if (empty($bind)) {
            return 0;
        }

        return $this->_updateOrDelete('update', clone $filters, $bind);
    }

    /**
     * Permet de supprimer la ou les propriétés de n messages avec la même valeur
     *
     * @param \Deo\Dao\IFilter $filters Le filtre
     *
     * @return int nombre de messages supprimé
     *
     * @throws Exception
     */
    protected function delete(Dao\IFilter $filters = null)
    {
        return $this->_updateOrDelete('delete', clone $filters);
    }

    /**
     * Permet de supprimer/maj la ou les propriétés de n messages avec la même valeur
     *
     * @param \Deo\Dao\IFilter $filters Le filtre
     * @param array            $bind   Liste des champs et leurs valeurs associés
     *
     * @return int nombre de messages supprimé/maj
     *
     * @throws Exception
     */
    private function _updateOrDelete($action, Dao\IFilter $filters, $bind = null)
    {
        $filters->properties = $this->_primary;
        $select = $this->_selectFromFilter($filters);
        $sql = $select->__toString();

        if (strstr($sql, 'JOIN ') === FALSE) {
            /**
             * @todo récupérer les wheres dans le select
             *
             * ??? $select->getPart(\Zend_Db_Select::WHERE)
             */
            $where = $this->getWhere($filters);
        } else {
            if (is_null($this->_primary)) {
                throw new \Exception('Primary is required');
            }

            $stmt = $this->getDb()->query($select->__toString());
            $ids = $stmt->fetchAll(\Zend_Db::FETCH_COLUMN);

            if (count($ids) == 0) {
                return 0;
            }
            /**
             * Build the UPDATE statement
             */
            $primaryCol = $this->_map[reset($this->_primary)];
            $where = array($primaryCol . ' IN (' . implode(',', $ids) . ')');
        }

        $result = null;
        switch ($action) {
            case 'update':
                $result = $this->getDb()->update($this->_tableName, $bind, $where);
                break;
            case 'delete':
                $result = $this->getDb()->delete($this->_tableName, $where);
                break;
            default:
                throw new Exception('invalid action ' . $action);
                break;
        }

        return $result;
    }

    /**
     * Inserts multiple rows into the table
     *
     * @param array $$datasMapped array(array) dans les tableaux à niveau deux, key: column name, value: value
     * @param boolean $ignore
     *
     * @return int affected rows
     *
     * @throws Exception
     *
     */
    protected function insertMulti($datas, $ignore = false)
    {
        if (count($datas) == 0) {
            return 0;
        }

        $datasMapped = array();
        //creation des messages
        foreach ($datas as $data) {
            ksort($data);
            $datasMapped[] = $this->getMapDatasNotIntoJoin($data);
        }

        $allValues = array();
        $columns = array();
        $bind = array();

        // Extract and quote col names from the array keys
        // of the first row
        $first = current($datasMapped);
        $columns = array();

        foreach (array_keys($first) as $column) {
            $columns[] = $this->getDb()->quoteIdentifier($column, true);
        }

        // Loop through data to extract values for binding
        foreach ($datasMapped as $rowData) {
            if (count($rowData) != count($columns)) {
                throw new \Invalid_Argument_Exception('Each row must have the same number of columns.');
            }

            $values = array();

            foreach ($rowData as $key => $value) {
                if ($value instanceof \Zend_Db_Expr) {
                    $values[] = $value->__toString();
                } else {
                    $values[] = '?';
                    $bind[] = $value;
                }
            }

            $allValues[] = "(" . implode(', ', $values) . ")";
        }

        // Build the insert statement
        $sql = 'INSERT ';
        if ($ignore) {
            $sql .= 'IGNORE ';
        }
        $sql .= 'INTO '
            . $this->getDb()->quoteIdentifier($this->_tableName, true)
            . "\n("
            . implode(', ', $columns)
            . ")\nVALUES\n"
            . implode(', ', $allValues);

        // Execute the statement and return the number of affected rows
        $stmt = $this->getDb()->query($sql, $bind);
        return $stmt->rowCount();
    }

    /**
     * Permet de ne récuperer que les identifiants des enregistrements sélectionnés par un filtre
     *
     * @param \Deo\Dao\IFilter $filters Les filtres de sélection
     *
     * @return array
     */
    public function fetchId(Dao\IFilter $filters)
    {
        $properties = $this->_primary;
        $select = $this->_selectFromFilter($properties);
        return $this->getDb()->fetchCol($select);
    }

    /**
     * Crée un objet Select à partir d'un filtre
     *
     * @param array $properties
     * @param array $where
     * @param int   $limit
     * @param int   $offset
     * @param array $sort
     * @param array $group
     *
     * @return \Zend_Db_Select
     */
    protected function _selectFromFilter($properties=array(), $where=array(), $limit=null,
        $offset=0, $sort=array(), $group=array())
    {
        // Création d'un select
        $select = $this->getDb()->select();

        // Définition de la table et des colonnes
        $select->from($this->_tableName, $this->getMapCols($properties, true));

        $joins = $this->_getJoins($where);
        foreach ($joins as $table => $join) {
            /*
             * 0: string    condition on join manadatory
             * 1: string    method (inner/left) default inner
             * 2: booelean  is table of column default true
             * 3: array     specific properties default null
             */
            if (!array_key_exists(0, $join)) {
                throw new \Exception('Invalid join condition');
            }
            $on = $join[0];
            $method = array_key_exists(1, $join) ? 'join' . ucfirst($join[1]) : 'joinInner';
            $properties = (array_key_exists(3, $join) || empty($join[3])) ? null : $this->getMapCols($join[3]);
            if (!method_exists($select, $method)) {
                throw new \Exception('Invalid join request: ' . $join[1]);
            }

            $select->$method($table, $on, $properties);
        }

        // Gestion des filtres
        $wheres = $this->getWhere($where);
        foreach ($wheres as $where) {
            $select->where($where);
        }

        $this->_specificFilters($select, $filters);

        // Limitation du nombre de résultat
        if (isset($limit) && isset($offset)) {
            $select->limit($limit, $offset);
        }

        // Gestion du tri
        if (is_array($sort) && count($sort) >= 1) {
            $select->order($this->getMapOrder($sort));
        }

        if (is_array($group) && count($group) >= 1) {
            $select->group($this->getMapCols($group));
        }

        return $select;
    }
}
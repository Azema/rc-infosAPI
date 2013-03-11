<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

namespace Rca\Db;

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
abstract class AbstractDb
{
    const FETCH_NUM = 3;
    /**
     * Use the INT_TYPE, BIGINT_TYPE, and FLOAT_TYPE with the quote() method.
     */
    const INT_TYPE    = 0;
    const BIGINT_TYPE = 1;
    const FLOAT_TYPE  = 2;

    /**
     * Connecteur à la BDD
     *
     * @var PDO
     */
    protected static $_adapter;

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * self::INT_TYPE, self::BIGINT_TYPE, or self::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        'INT'                => self::INT_TYPE,
        'INTEGER'            => self::INT_TYPE,
        'MEDIUMINT'          => self::INT_TYPE,
        'SMALLINT'           => self::INT_TYPE,
        'TINYINT'            => self::INT_TYPE,
        'BIGINT'             => self::BIGINT_TYPE,
        'SERIAL'             => self::BIGINT_TYPE,
        'DEC'                => self::FLOAT_TYPE,
        'DECIMAL'            => self::FLOAT_TYPE,
        'DOUBLE'             => self::FLOAT_TYPE,
        'DOUBLE PRECISION'   => self::FLOAT_TYPE,
        'FIXED'              => self::FLOAT_TYPE,
        'FLOAT'              => self::FLOAT_TYPE
    );

    /**
     * Connecteur à la BDD
     *
     * @var PDO
     */
    protected $_db;

    /**
     * Nom de la table
     *
     * @var string
     */
    protected $_name;

    /**
     * Nom de la classe du modèle objet
     *
     * @var string
     */
    protected $_modelClass;

    /**
     * MetaData de la table
     *
     * @var array
     */
    protected $_metadata = array();

    /**
     * Nom de la/les clef(s) primaire(s)
     *
     * @var array
     */
    protected $_primary = array();

    /**
     * Table de mapping entre les propriétés et les colonnes
     *
     * @var array
     */
    protected $_map = array();

    public function __construct()
    {
        // Check la présence du connecteur
        if (null == self::$_adapter) {
            throw new \Exception('No adapter found for ' . get_class($this));
        }
        $this->_db = self::$_adapter;
        // Check le nom de la table
        if (! $this->_name) {
            $className = get_class($this);
            $this->_name = strtolower(substr($className, strrpos($className, '\\')+1));
        }
        $this->_setupMetadata();
    }

    /**
     * Specifie le connecteur à la BDD
     *
     * @param PDO $adapter Le connecteur
     *
     * @return void
     */
    public static function setAdapter($adapter)
    {
        self::$_adapter = $adapter;
    }

    /**
     * Retourne l'ensemble des enregistrements de la table
     *
     * @return \Rca\Model\AbstractModel[]
     */
    public function fetchAll()
    {
        $stmt = $this->_query();
        if (!$stmt) {
            return array();
        }
        return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->_modelClass);
    }

    /**
     * Retourne le premier enregistrement trouvé dans la table
     *
     * @param array $where Tableau clef valeur pour la clause where
     *
     * @return \Rca\Model\AbstractModel
     */
    public function fetchOne($where = array())
    {
        $stmt = $this->_query($where);
        if (!$stmt) {
            return false;
        }
        return $stmt->fetchObject($this->_modelClass);
    }

    /**
     * Retourne tous les enregistrements trouvé correspondant à la clause $where dans la table
     *
     * @param array $where Tableau clef valeur pour la clause where
     *
     * @return \Rca\Model\AbstractModel[]
     */
    public function fetch($where = array())
    {
        $stmt = $this->_query($where);
        if (!$stmt) {
            return array();
        }
        return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->_modelClass);
    }

    protected function _query($where = array(), $columns = array())
    {
        $query = 'SELECT ' . $this->_mapSelectColumns($columns) . ' FROM '
            . $this->quoteIdentifier($this->_name) . ' WHERE '
            . $this->_mapWhereColumns($where) . ';';
        $stmt = $this->_db->query($query);
        return $stmt;
    }

    /**
     * Définit le nom des colonnes à récuperer pour remplir l'objet modèle
     * pour les requêtes SQL
     *
     * @param array $columns Les colonnes souhaitées
     *
     * @return string
     */
    protected function _mapSelectColumns($columns = array())
    {
        if (empty($columns)) {
            $columns = array_values($this->_map);
        } else {
            $columns = array_intersect($this->_map, $columns);
        }
        $selectColumns = array();
        foreach ($columns as $name) {
            $colName = array_search($name, $this->_map);
            $selectColumns[] = $this->quoteIdentifier($colName) . ' AS ' . $this->_db->quote($name);
        }
        return implode(',', $selectColumns);
    }

    /**
     * Définit la clause where à partir des noms de colonnes et des valeurs recherchées.
     *
     * @param array $columns Tableau clef valeur des elements recherchés
     *
     * @return string
     */
    protected function _mapWhereColumns($columns = array())
    {
        $columnNames = array_intersect($this->_map, array_keys($columns));
        $keyColumns = array_flip($this->_map);
        if (empty($columnNames)) {
            return '1';
        }
        $whereColumns = array();
        foreach ($columnNames as $column) {
            $whereColumns[] = $this->quoteIdentifier($keyColumns[$column]) 
                . ' = ' . $this->_db->quote($columns[$column]);
        }
        return implode(' AND ', $whereColumns);
    }

    /**
     * Quote les noms de table et de colonne
     *
     * @param string $value La valeur à quoter
     *
     * @return string
     */
    public function quoteIdentifier($value)
    {
        $q = '`';
        return ('`' . str_replace('`', '``', $value) . '`');
    }

    /**
     * Initialise les metadata, la table de mapping des colonnes
     * et les clefs primaires.
     *
     * @return void
     */
    protected function _setupMetadata()
    {
        if (count($this->_metadata) > 0) {
            return true;
        }
        // Fetch metadata from the adapter's describeTable() method
        $metadata = $this->_describe();
        $first = reset($metadata);
        $prefix = substr($first['COLUMN_NAME'], 0, strpos($first['COLUMN_NAME'], '_')+1);
        foreach ($metadata as $column) {
            $this->_map[$column['COLUMN_NAME']] = str_replace($prefix, '', $column['COLUMN_NAME']);
            if ($column['PRIMARY']) {
                $this->_primary[] = $column['COLUMN_NAME'];
            }
        }

        // Assign the metadata to $this
        $this->_metadata = $metadata;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function _describe()
    {
        // @todo  use INFORMATION_SCHEMA someday when MySQL's
        // implementation has reasonably good performance and
        // the version with this improvement is in wide use.

        $sql = 'DESCRIBE ' . $this->quoteIdentifier($this->_name);
        $stmt = $this->_db->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $result = $stmt->fetchAll(self::FETCH_NUM);

        $field   = 0;
        $type    = 1;
        $null    = 2;
        $key     = 3;
        $default = 4;
        $extra   = 5;

        $desc = array();
        $i = 1;
        $p = 1;
        foreach ($result as $row) {
            list($length, $scale, $precision, $unsigned, $primary, $primaryPosition, $identity)
                = array(null, null, null, null, false, null, false);
            if (preg_match('/unsigned/', $row[$type])) {
                $unsigned = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'decimal';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row[$type], $matches)) {
                $row[$type] = 'float';
                $precision = $matches[1];
                $scale = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                // The optional argument of a MySQL int type is not precision
                // or length; it is only a hint for display width.
            }
            if (strtoupper($row[$key]) == 'PRI') {
                $primary = true;
                $primaryPosition = $p;
                if ($row[$extra] == 'auto_increment') {
                    $identity = true;
                } else {
                    $identity = false;
                }
                ++$p;
            }
            $desc[(string)$row[$field]] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => strtolower($this->_name),
                'COLUMN_NAME'      => (string)$row[$field],
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $row[$default],
                'NULLABLE'         => (bool) ($row[$null] == 'YES'),
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => $unsigned,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->fetchCol('SHOW TABLES');
    }
}

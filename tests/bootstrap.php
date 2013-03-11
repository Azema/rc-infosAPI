<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

define('TEST_PATH', dirname(__FILE__));

define('FIXTURES_PATH', dirname(__FILE__) . '/fixtures/');

defined('ENVIRONMENT_PATH') || define(
    'ENVIRONMENT_PATH',
    (getenv('ENVIRONMENT_PATH') ? getenv('ENVIRONMENT_PATH') : FIXTURES_PATH . '/config/environment.php')
);

// Define application environment
defined('APPLICATION_ENV') || define(
    'APPLICATION_ENV',
    getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'
);

set_include_path(
    implode(
        PATH_SEPARATOR, array(
            APPLICATION_PATH,
            get_include_path(),
    ))
);

require_once APPLICATION_PATH . '/autoload.php';
require_once APPLICATION_PATH . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL | E_STRICT);
$pdo = getPdo($config = include ENVIRONMENT_PATH);
$pdo->exec(file_get_contents(APPLICATION_PATH . '/config/db/migrate/schema.txt'));

function createDsn($config)
{
    if (!array_key_exists('db', $config)) {
        throw new \Exception('no config found for Phactory');
    }
    $dsn = 'mysql:dbname=' . $config['db']['database'] . ';';
    if (array_key_exists('socket', $config['db'])) {
        $dsn .= 'socket=' . $config['db']['socket'];
    } else {
        if (!array_key_exists('host', $config['db'])) {
            $config['db']['host'] = 'localhost';
        }
        if (!array_key_exists('port', $config['db'])) {
            $config['db']['port'] = '3306';
        }
        $dsn .= 'host=' . $config['db']['host'] . ';'
            . 'port=' . $config['db']['port'];
    }
    if (array_key_exists('charset', $config['db'])) {
        $dsn .= ';charset=' . $config['db']['charset'];
    }
    return $dsn;
}

function getPdo($config)
{
    global $pdo;
    if (isset($pdo)) {
        return $pdo;
    }
    $dsn = createDsn($config);
    $options = array();
    if (array_key_exists('options', $config['db'])) {
        $options = $config['db']['options'];
    }
    $pdo = new \PDO($dsn, $config['db']['user'], $config['db']['password'], $options);
    return $pdo;
}
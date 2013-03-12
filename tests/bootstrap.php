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
            APPLICATION_PATH . '/library',
            get_include_path(),
    ))
);

require_once APPLICATION_PATH . '/autoload.php';
require_once APPLICATION_PATH . '/vendor/autoload.php';

ini_set('error_reporting', E_ALL | E_STRICT);
$config = include ENVIRONMENT_PATH;
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
$db = new \Zend_Db_Adapter_Pdo_Mysql($config['db']);
$pdo = $db->getConnection();
$pdo->exec(file_get_contents(APPLICATION_PATH . '/config/db/migrate/schema.txt'));
\Rca\Db\AbstractDb::setDefaultAdapter($db);

$frontendOptions = array('automatic_serialization' => true);
 
$backendOptions  = array('cache_dir' => TEST_PATH . '/tmp/');
require_once 'Zend/Cache.php';
$cache = \Zend_Cache::factory('Core',
                             'File',
                             $frontendOptions,
                             $backendOptions);
\Rca\Db\AbstractDb::setDefaultMetadataCache($cache);

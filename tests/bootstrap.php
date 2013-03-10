<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

defined('APPLICATION_PATH') || define(
    'APPLICATION_PATH',
    realpath(dirname(__FILE__) . '/../')
);

defined('ENVIRONMENT_PATH') || define(
    'ENVIRONMENT_PATH',
    (getenv('ENVIRONMENT_PATH') ? getenv('ENVIRONMENT_PATH') : APPLICATION_PATH . '/config/environment.ini')
);

define('TEST_PATH', dirname(__FILE__));

define('FIXTURES_PATH', dirname(__FILE__) . '/fixtures/');

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

function __autoload($className) {
    if ('\\' == $className[0]) {
        $className = substr($className, 1);
    }

    if (false !== $pos = strrpos($className, '\\')) {
        // namespaced class name
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($className, 0, $pos)) . DIRECTORY_SEPARATOR;
        $className = substr($className, $pos + 1);
    } else {
        // PEAR-like class name
        $classPath = null;
        $className = $className;
    }

    if (substr($classPath, 0, 5) == 'Rca') {
        $classPath = substr($classPath, 6);
    }
    $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $paths = explode(PATH_SEPARATOR, get_include_path());
    $class = null;
    foreach ($paths as $dir) {
        if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
            $class = $dir . DIRECTORY_SEPARATOR . $classPath;
            break;
        }
    }
    if ($class) {
        require_once $class;
        return true;
    }
    return false;
};

ini_set('error_reporting', E_ALL | E_STRICT);

<?php

/**
 * rc-infos (https://github.com/Azema/rc-infoDroid)
 *
 * @link      https://github.com/Azema/rc-infosAPI for the canonical source repository
 * @license   https://github.com/Azema/rc-infosAPI/LICENCE New BSD License
 * @copyright Copyright (c) 2013 Manuel Hervo. (https://github.com/Azema)
 */

return array(
    'db' => array(
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'rcinfos_test',
        'user' => 'rcinfos',
        'password' => 'rcinfos',
        'charset' => 'utf8',
        'options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        ),
    ),
);

<?php

namespace Rca\Db;

use \Phactory\Sql\Phactory;

/**
 * @group Db
 */
abstract class AbstractTestDb extends \PHPUnit_Framework_TestCase
{
	protected static $_phactory;

	public static function setUpBeforeClass()
	{
		$config = include ENVIRONMENT_PATH;
		$pdo = getPdo($config);
		self::$_phactory = new Phactory($pdo);
		\Rca\Db\AbstractDb::setAdapter($pdo);
	}

	protected function tearDown()
	{
		self::$_phactory->recall();
		$this->_object = null;
		parent::tearDown();
	}
}

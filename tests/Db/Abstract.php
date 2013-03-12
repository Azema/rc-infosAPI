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
		$pdo = \Rca\Db\AbstractDb::getDefaultAdapter()->getConnection();
		self::$_phactory = new Phactory($pdo);
	}

	public static function tearDownAfterClass()
	{
		self::$_phactory->reset();
		parent::tearDownAfterClass();
	}

	protected function tearDown()
	{
		self::$_phactory->recall();
		$this->_object = null;
		parent::tearDown();
	}
}

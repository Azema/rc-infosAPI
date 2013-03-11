<?php

namespace Rca\Db;

require_once __DIR__ . '/Abstract.php';

use \Phactory\Sql\Phactory;

/**
 * @group Db
 */
class ClubsTest extends AbstractTestDb
{
	/**
	 * @var \Rca\Db\Clubs
	 */
	protected $_object;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		// Définition d'un enregistrement type d'un club
		self::$_phactory->define('club', 
		    array(
		        'clb_name' => 'Racing$n',
		        'clb_address' => '3 rue Rapatel',
		        'clb_postCode' => '35000',
		        'clb_city' => 'Rennes',
		        'clb_email' => 'cpbracing35@free.fr',
		        'clb_phone' => '0299123456',
		        'clb_gps' => '48.0978,-1.6497',
		        'clb_siteWeb' => 'http://cpbvrc.eklablog.com/',
		        'clb_createdAt' => '2013-03-10 16:03:00',
		    ),
		    array(
		        'league' => self::$_phactory->manyToOne('league', 'clb_leg_id', 'leg_id'),
		    )
		);
		// Définition d'un enregistrement type d'une ligue
		self::$_phactory->define('league', 
		    array(
		        'leg_name' => 'ligue $n',
		        'leg_president' => 'Henry SERBOURCE',
		        'leg_address' => '44 rue Raymond Guillemot',
		        'leg_postCode' => '56600',
		        'leg_city' => 'Lanester',
		        'leg_email' => 'henry.serbource@gmail.com',
		        'leg_phone' => '0612334323',
		        'leg_siteWeb' => 'http://ligue$n.com/',
		        'leg_createdAt' => '2013-03-10 01:31:00',
		    )
		);
	}

	protected function setUp()
	{
		parent::setUp();
		$this->_object = new Clubs();
	}

	public function testFetchAll()
	{
		$nbClubs = 5;
        $clubs = array();
        $league = self::$_phactory->create('league');
        for($i = 1; $i <= $nbClubs; $i++) {
            // create a row in the db with age = $i, and store a Phactory_Row object
            $clubs[] = self::$_phactory->createWithAssociations('club', array('league' => $league));
        }
        $actual = $this->_object->fetchAll();
        $this->assertNotEmpty($actual);
        $this->assertEquals($nbClubs, count($actual));
        foreach ($actual as $object) {
        	$this->assertInstanceof('\Rca\Model\Club', $object);
        }
	}

	public function testFetchRow()
	{
		$email = 'cpbracing35@free.fr';
        $league = self::$_phactory->create('league');
		$club = self::$_phactory->createWithAssociations('club', array('league' => $league));
		$actual = $this->_object->fetchRow(array('email = ?' => $email));
    	$this->assertInstanceof('\Rca\Model\Club', $actual);
		$this->assertEquals($email, $actual->email);
		$actual = $this->_object->fetchRow(array('email = ?' => 'wrong'));
		$this->assertFalse($actual);
	}

	public function testFetchAllWithOneCondition()
	{
		$nbClubs = 3;
		$clubs = array();
        $league = self::$_phactory->create('league');
        for($i = 1; $i <= $nbClubs; $i++) {
            // create a row in the db with age = $i, and store a Phactory_Row object
            $clubs[] = self::$_phactory->createWithAssociations('club', array('league' => $league));
        }
		$actual = $this->_object->fetchAll(array('leagueId = ?' => $league->leg_id));
		$this->assertNotEmpty($actual);
		$this->assertEquals($nbClubs, count($actual));
		foreach ($actual as $club) {
			$this->assertInstanceof('\Rca\Model\Club', $club);
			$this->assertEquals($league->leg_id, $club->leagueId);
		}
		$actual = $this->_object->fetchAll(array('leagueId = ?' => 9999999));
		$this->assertInstanceof('\Zend_Db_Table_Rowset', $actual);
		$this->assertEquals(0, count($actual));
	}

	public function testFind()
	{
        $league = self::$_phactory->create('league');
        // create a row in the db with age = $i, and store a Phactory_Row object
        $club = self::$_phactory->createWithAssociations('club', array('league' => $league));
        $actual = $this->_object->find($club->clb_id);
        $this->assertInstanceof('\Zend_Db_Table_Rowset', $actual);
        $this->assertEquals(1, count($actual));
        $actual = $actual->current();
        $this->assertInstanceof('\Rca\Model\Club', $actual);
		$this->assertEquals($club->clb_id, $actual->id);
	}
}

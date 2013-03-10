<?php

/**
 * @group Model
 */
class LeagueTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \Rca\Model\League
	 */
	protected $_object;

	protected function setUp()
	{
		parent::setUp();
		$this->_object = new \Rca\Model\League();
	}

	protected function tearDown()
	{
		$this->_object = null;
		parent::tearDown();
	}

	public function testSetProperties()
	{
		$properties = array(
			'id' => 19,
			'name' => 'Ligue 19',
            'president' => 'Henry SERBOURCE',
            'address' => '44 rue Raymond Guillemot',
			'postCode' => '56600',
			'city' => 'Lanester',
			'email' => 'henry.serbource@gmail.com',
			'phone' => '0612334323',
			'siteWeb' => 'http://ligue19.com/',
			'createdAt' => '2013-03-10 01:31:00',
			'updatedAt' => '2013-03-10 01:35:00',
		);
		foreach ($properties as $key => $value) {
			$this->assertNull($this->_object->{$key});
			$this->_object->{$key} = $value;
			$this->assertSame($value, $this->_object->{$key});
		}
	}

	public function testFillFromArray()
	{
		$properties = array(
			'id' => 19,
			'name' => 'Ligue 19',
            'president' => 'Henry SERBOURCE',
            'address' => '44 rue Raymond Guillemot',
			'postCode' => '56600',
			'city' => 'Lanester',
			'email' => 'henry.serbource@gmail.com',
			'phone' => '0612334323',
			'siteWeb' => 'http://ligue19.com/',
			'createdAt' => '2013-03-10 01:31:00',
			'updatedAt' => '2013-03-10 01:35:00',
		);
		$actual = $this->_object->fillFromArray($properties);
		$this->assertInstanceof('\Rca\Model\League', $actual);
		$this->assertSame($this->_object, $actual);
		foreach ($properties as $key => $value) {
			$this->assertSame($value, $actual->{$key});
		}
    }

    public function testHasClubs()
    {
        $this->assertFalse($this->_object->hasClubs());
        $club = new \Rca\Model\Club();
        $this->_object->clubs = array($club);
        $this->assertTrue($this->_object->hasClubs());
    }
}

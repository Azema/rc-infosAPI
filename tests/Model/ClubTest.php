<?php

/**
 * @group Model
 */
class ClubTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \Rca\Model\Club
	 */
	protected $_object;

	protected function setUp()
	{
		parent::setUp();
		$this->_object = new \Rca\Model\Club();
	}

	protected function tearDown()
	{
		$this->_object = null;
		parent::tearDown();
	}

	public function testSetProperties()
	{
		$properties = array(
			'id' => 14,
			'name' => 'CPB racing35',
			'address' => '3 rue rapatel',
			'postCode' => '35000',
			'city' => 'Rennes',
			'email' => 'racing35@free.fr',
			'phone' => '0299456789',
			'gps' => '48.0978,-1.6497',
			'siteWeb' => 'http://cpbvrc.eklablog.com/',
			'leagueId' => 4,
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
			'id' => 14,
			'name' => 'CPB racing35',
			'address' => '3 rue rapatel',
			'postCode' => '35000',
			'city' => 'Rennes',
			'email' => 'racing35@free.fr',
			'phone' => '0299456789',
			'gps' => '48.0978,-1.6497',
			'siteWeb' => 'http://cpbvrc.eklablog.com/',
			'leagueId' => 4,
			'createdAt' => '2013-03-10 01:31:00',
			'updatedAt' => '2013-03-10 01:35:00',
		);
		$actual = $this->_object->fillFromArray($properties);
		$this->assertInstanceof('\Rca\Model\Club', $actual);
		$this->assertSame($this->_object, $actual);
		foreach ($properties as $key => $value) {
			$this->assertSame($value, $actual->{$key});
		}
    }

    public function testIsAffiliated()
    {
        $this->assertFalse($this->_object->isAffiliated());
        $this->_object->leagueId = 1;
        $this->assertTrue($this->_object->isAffiliated());
    }

    public function testIsLocated()
    {
        $this->assertFalse($this->_object->isLocated());
        $this->_object->gps = '48.0978,-1.6497';
        $this->assertTrue($this->_object->isLocated());
    }
}

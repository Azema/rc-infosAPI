<?php

namespace Rca\Model;

/**
 * @group Model
 */
class TrackTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Rca\Model\Track
	 */
	protected $_object;

	protected function setUp()
	{
		parent::setUp();
		$this->_object = new \Rca\Model\Track();
	}

	protected function tearDown()
	{
		$this->_object = null;
		parent::tearDown();
	}

	public function testSetProperties()
	{
		$properties = array(
			'id' => 4,
            'clubId' => 14,
            'type' => 'TT',
            'motors' => array('brushless','thermique'),
            'coating' => 'terre,moquette',
            'length' => 120,
            'width' => 4,
			'address' => 'Route de Rennes',
			'postCode' => '35220',
			'city' => 'Chateaubourg',
            'gps' => '48.10203,-1.42849',
            'equipments' => 'électicité,stands,podium 12M,buvette',
			'createdAt' => '2013-03-10 01:31:00',
			'updatedAt' => '2013-03-10 01:35:00',
		);
        foreach ($properties as $key => $value) {
            if (is_array($value)) {
                $this->assertEmpty($this->_object->{$key});
            } else {
                $this->assertNull($this->_object->{$key});
            }
			$this->_object->{$key} = $value;
			$this->assertSame($value, $this->_object->{$key});
        }
        $this->_object->wrong = "unknown";
        $this->assertFalse(isset($this->_object->wrong));
        $this->assertFalse(property_exists($this->_object, 'wrong'));
	}

	public function testFillFromArray()
	{
		$properties = array(
			'id' => 4,
            'clubId' => 14,
            'type' => 'TT',
            'motors' => array('brushless','thermique'),
            'coating' => 'terre,moquette',
            'length' => 120,
            'width' => 4,
			'address' => 'Route de Rennes',
			'postCode' => '35220',
			'city' => 'Chateaubourg',
            'gps' => '48.10203,-1.42849',
            'equipments' => 'électicité,stands,podium 12M,buvette',
			'createdAt' => '2013-03-10 01:31:00',
            'updatedAt' => '2013-03-10 01:35:00',
            'wrong' => 'unknown',
		);
		$actual = $this->_object->fillFromArray($properties);
		$this->assertInstanceof('\Rca\Model\Track', $actual);
		$this->assertSame($this->_object, $actual);
        foreach ($properties as $key => $value) {
            if ($key == 'wrong') {
                $this->assertFalse(isset($this->_object->{$key}));
                $this->assertFalse(property_exists($this->_object, $key));
            } else {
                $this->assertSame($value, $actual->{$key});
            }
		}
    }
    
    public function testIsLocated()
    {
        $this->assertFalse($this->_object->isLocated());
        $this->_object->gps = '48.10203,-1.42849';
        $this->assertTrue($this->_object->isLocated());
    }

    public function testHasClub()
    {
        $this->assertFalse($this->_object->hasClub());
        $this->_object->clubId = 1;
        $this->assertTrue($this->_object->hasClub());
    }

    public function testMotorAllowed()
    {
        $this->assertFalse($this->_object->motorAllowed('brushless'));
        $this->_object->addMotor('brushless');
        $this->assertTrue($this->_object->motorAllowed('brushless'));
        $this->assertFalse($this->_object->motorAllowed('thermique'));
        $this->_object->addMotor('thermique');
        $this->assertTrue($this->_object->motorAllowed('thermique'));
    }
}

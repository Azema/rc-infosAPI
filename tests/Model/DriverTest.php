<?php

namespace Rca\Model;

/**
 * @group Model
 */
class DriverTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Rca\Model\Driver
	 */
	protected $_object;

	protected function setUp()
	{
		parent::setUp();
		$this->_object = new \Rca\Model\Driver();
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
            'firstName' => 'Jérôme',
            'lastName' => 'Sartel',
            'clubId' => 1,
            'license' => '13191',
            'licenseType' => 'Nationale',
            'email' => 'jsartel@exemple.com',
            'phone' => '0321026965',
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
            'firstName' => 'Jérôme',
            'lastName' => 'Sartel',
            'clubId' => 1,
            'license' => '13191',
            'licenseType' => 'Nationale',
            'email' => 'jsartel@exemple.com',
            'phone' => '0321026965',
			'createdAt' => '2013-03-10 01:31:00',
			'updatedAt' => '2013-03-10 01:35:00',
		);
        $actual = $this->_object->fillFromArray($properties);
        $this->assertInstanceof('\Rca\Model\Driver', $actual);
        $this->assertSame($this->_object, $actual);
        foreach ($properties as $key => $value) {
            $this->assertSame($value, $actual->{$key});
        }
    }

    public function testIsLicensee()
    {
        $this->assertFalse($this->_object->isLicensee());
        $this->_object->license = '13191';
        $this->assertTrue($this->_object->isLicensee());
    }

    public function testHasClub()
    {
        $this->assertFalse($this->_object->hasClub());
        $this->_object->clubId = 1;
        $this->assertTrue($this->_object->hasClub());
    }

    public function testToString()
    {
        $this->assertEmpty((string)$this->_object);
        $firstName = 'Jérôme';
        $lastName = 'Sartel';
        $this->_object->firstName = $firstName;
        $this->assertEquals($firstName, (string)$this->_object);
        $this->_object->lastName = $lastName;
        $this->assertEquals($firstName . ' ' . $lastName, (string)$this->_object);
        $this->_object->firstName = null;
        $this->assertEquals($lastName, (string)$this->_object);
        $this->assertEquals($lastName, $this->_object->__toString());
    }

    public function testIsset()
    {
        $this->assertFalse(isset($this->_object->firstName));
        $this->assertTrue(property_exists($this->_object, 'firstName'));
        $this->_object->firstName = 'Jérôme';
        $this->assertTrue(isset($this->_object->firstName));
        $this->assertNotEmpty($this->_object->firstName);
    }

    public function testUnset()
    {
        $this->_object->firstName = 'Jérôme';
        $this->assertTrue(isset($this->_object->firstName));
        unset($this->_object->firstName);
        $this->assertFalse(isset($this->_object->firstName));
        $this->assertTrue(property_exists($this->_object, 'firstName'));
    }
}

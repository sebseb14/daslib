<?php
/**
 * Dasoft Toolkit
 * 
 * @category    Dasoft
 * @package     UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright   Copyright (c) 2010-2012 Daniel Arsenault
 * @license     BSD License
 * @version     $Id$-expansion
 */

use Dasoft\Util\Object;

/**
 * @category    Dasoft
 * @package     UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 */
class ObjectTest extends PHPUnit_Framework_TestCase
{	
	/**
	 * Test for Object creation
	 */
	public function testCreation()
	{
		$obj = new ObjectStub();
		$this->assertInstanceOf('Dasoft\\Util\\Object', $obj);
	}
	
	/**
	 * Test for Object creation with properties
	 */
	public function testCreationProperties()
	{
		$obj = new ObjectStub(array('prop1' => 1));
		$this->assertInstanceOf('Dasoft\\Util\\Object', $obj);
		$this->assertEquals(1, $obj->prop1);
		
		$obj = new ObjectStub(array('prop2' => 2), ObjectStub::MODE_FLEXIBLE);
		$this->assertInstanceOf('Dasoft\\Util\\Object', $obj);
		$this->assertEquals(2, $obj->prop2);
		$this->assertEquals(null, $obj->prop3);
		
		$this->setExpectedException('ErrorException', 'Undefined property');
		$obj = new ObjectStub(array('prop2' => 2));
	}
	
	public function providerPropertyAccessor()
	{
		return array(
			array('prop1', "", 'prop1', null, false),
			array('prop1', 1, 'prop1', 1, false),
			array('prop2', 2, 'prop2', 2, true),
			array('prop1', null, 'devnull', 'null', false),
			array('devnull', 'foo', 'devNullTest', 'foo', false),
		);
	}
	
	/**
	 * Test for Object property magic accessor
	 * 
	 * @dataProvider providerPropertyAccessor
	 */
	public function testPropertyAccessor($setProp, $setValue, $getProp, $getValue, $flexible)
	{
		if($flexible)
		{
			$obj = new ObjectStub(null, ObjectStub::MODE_FLEXIBLE);
		}
		else
		{
			$obj = new ObjectStub();
		}
		
		$obj->{$setProp} = $setValue;
		$this->assertEquals($getValue, $obj->{$getProp});
	}
	
	public function providerPropertyAccessorException()
	{
		return array(
			array('prop2', 2, 'ErrorException', 'Undefined property'),
			array('_private', 2, 'ErrorException', 'Cannot access private property'),
			array('_protected', 2, 'ErrorException', 'Cannot access protected property'),
		);
	}
	
	/**
	 * Test for Object property magic accessor
	 * 
	 * @dataProvider providerPropertyAccessorException
	 */
	public function testPropertySetException($prop, $value, $exception, $message)
	{
		$obj = new ObjectStub();
		
		$this->setExpectedException($exception, $message);
		$obj->{$prop} = $value;
	}
	
	/**
	 * Test for Object property magic accessor
	 *
	 * @dataProvider providerPropertyAccessorException
	 */
	public function testPropertyGetException($prop, $value, $exception, $message)
	{
		$obj = new ObjectStub();
	
		$this->setExpectedException($exception, $message);
		$tmp = $obj->{$prop};
	}
	
	public function testPropertiesAccessor()
	{
		$a = array('prop1' => null);
		$b = array('one' => '1', 'two' => 2);
		$array = $a + $b + array( 'three' => 'three');
		$obj = new ObjectStub($array, ObjectStub::MODE_FLEXIBLE);
		$this->assertEquals($array, $obj->getProperties());
		$this->assertEquals(array_filter($array), $obj->getProperties(null, true));
		$this->assertEquals($array['two'], $obj->getProperties('two'));
		$this->assertEquals($a + $b, $obj->getProperties(array('prop1', 'one','two')));
		$this->assertEquals($b, $obj->getProperties(array('prop1', 'one','two'), true));
		$this->assertNull($obj->getProperties('missing'));
	}
}

class ObjectStub extends Object
{
	public $public = 'public';
	private $_private = null;
	protected $_protected = null;
	protected $_object_properties = array('prop1' => null);
	
	public function getProperties($name = null, $ignoreNulls = false)
	{
		return $this->_getObjectProperties($name, $ignoreNulls);
	}
	
	protected function __set_devnull($value){ $this->_object_properties['devNullTest'] = $value; }
	protected function __get_devnull(){ return 'null'; }
}
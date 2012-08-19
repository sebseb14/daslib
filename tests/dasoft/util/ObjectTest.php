<?php
/**
 * Dasoft Toolkit
 * 
 * @category    Dasoft
 * @package     Dasoft\Util\Collection
 * @subpackage  UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright   Copyright (c) 2010-2011 Dasoft Inc. (http://www.dasoft.ca)
 * @license     http://dtk.dasoft.ca/license
 * @version     $Id: CollectionTest.php 5 2011-07-09 17:50:09Z darsenault $
 */

namespace Dasoft\Util;

/**
 * @category    Dasoft
 * @package     Dasoft\Util\Collection
 * @subpackage  UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright   Copyright (c) 2010-2011 Dasoft Inc. (http://www.dasoft.ca)
 * @license     http://dtk.dasoft.ca/license
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
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
	
	public function testPropertyAccessor()
	{
		$obj = new ObjectStub();
		$obj->prop1 =  1;
		$this->assertEquals(1, $obj->prop1);
		$this->setExpectedException('ErrorException', 'Undefined property');
		$obj->prop2 = 2;
		
		$obj = new ObjectStub(null, ObjectStub::MODE_FLEXIBLE);
		$obj->prop2 =  2;
		$this->assertEquals(2, $obj->prop2);
		
		$obj = new ObjectStub();
		$this->setExpectedException('Exception', 'TEST SET DEVNULL ASSERTION PASSED');
		$obj->devnull = 'foo';
		
		$this->assertEquals('null', $obj->devnull);
	}
}

class ObjectStub extends Object
{
	protected $_object_properties = array('prop1' => null);
	
	protected function __set_devnull(){throw new Exception('TEST SET DEVNULL ASSERTION PASSED'); }
	protected function __get_devnull(){ return 'null'; }
}
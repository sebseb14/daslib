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

use PHPUnit_Framework_TestCase;

/**
 * @category    Dasoft
 * @package     Dasoft\Util\Collection
 * @subpackage  UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright    Copyright (c) 2010-2011 Dasoft Inc. (http://www.dasoft.ca)
 * @license      http://dtk.dasoft.ca/license
 */
class CollectionTest extends PHPUnit_Framework_TestCase
{
	const ITEM_STRING_1 = 'b026324c6904b2a9cb4b88d6d61c81d1';
	const ITEM_STRING_2 = '26ab0db90d72e28ad0ba1e22ee510510';
	const ITEM_STRING_3 = '6d7fce9fee471194aa8b5b6e47267f03';
	const ITEM_STRING_4 = '48a24b70a0b376535542b996af517398';
	const ITEM_STRING_5 = '1dcca23355272056f04fe8bf20edfce0';
	
	const NON_ITEM_STRING_1 = '60b725f10c9c85c70d97880dfe8191b3';
	const NON_ITEM_STRING_2 = '3b5d5c3712955042212316173ccf37be';
	const NON_ITEM_STRING_3 = '2cd6ee2c70b0bde53fbe6cac3c8b8bb1';
	
	/**
	 * Test for Collection object creation
	 */
	public function testCreation()
	{
		$coll = new Collection();
		$this->assertInstanceOf('Dasoft\Util\Collection', $coll);
	}
	
	/**
	 * Test for Collection object creation with an array
	 */
	public function testCreationArray()
	{
		$a = array(self::ITEM_STRING_2, self::ITEM_STRING_3);
		$coll = new Collection($a);
		$this->assertInstanceOf('Dasoft\Util\Collection', $coll);
		$this->assertSame($a, (array)$coll);
		
		return $coll;
	}
	
	/**
	 * Test for Collection object creation with an array
	 * 
	 * @depends testCreationArray
	 */
	public function testCreationCollection($arg)
	{
		$coll = new Collection($arg);
		$this->assertInstanceOf('Dasoft\Util\Collection', $coll);
		$this->assertEquals($arg, $coll);
	}
	
	/**
	 * Test for Collection object creation with an array
	 */
	public function testCreationTyped()
	{
		$coll = new Collection(null, 'integer');
		$coll->add(123);
		$this->assertContains(123, $coll);
		$coll->add('string', 123);
		$this->assertContains(123, $coll);
		
		$this->setExpectedException('InvalidArgumentException');
		$coll->add(123.456);
		$coll->add('456');
		$coll->add('string');
		
		$this->setExpectedException('InvalidArgumentException');
		$coll = new Collection(array(123, 456, 'string'), 'string');
	}
	
	/**
	 * Test for Add
	 */
	public function testAdd()
	{
		$coll = new Collection();
		
		$arg = self::ITEM_STRING_1;
		$coll->add($arg);
		$this->assertContains($arg, $coll);
	}
	
	/**
	 * Test for addAll with a Collection
	 */
	public function testAddAllCollection()
	{
		$coll = new Collection();
		
		$c = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		// Add Collection
		$coll->addAll($c);
		$this->assertContains(self::ITEM_STRING_2, $coll);
		$this->assertContains(self::ITEM_STRING_3, $coll);
	}
	
	/**
	 * Test for addAll with an array
	 */
	public function testAddAllArray()
	{
		$coll = new Collection();
		
		$a = array(self::ITEM_STRING_4, self::ITEM_STRING_5);
		// Add Array
		$coll->addAll($a);
		$this->assertContains(self::ITEM_STRING_4, $coll);
		$this->assertContains(self::ITEM_STRING_5, $coll);
	}
	
	/**
	 * Test for contains
	 */
	public function testContains()
	{
		$coll = new Collection(array(self::ITEM_STRING_1));
		$this->assertTrue($coll->contains(self::ITEM_STRING_1));
		$this->assertFalse($coll->contains(self::NON_ITEM_STRING_1));
	}
	
	/**
	 * Test for containsAll
	 */
	public function testContainsAll()
	{
		$coll = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		
		$this->assertTrue($coll->containsAll(array(self::ITEM_STRING_2,self::ITEM_STRING_3)));
		$this->assertTrue($coll->containsAll(new Collection(array(self::ITEM_STRING_2,self::ITEM_STRING_3))));
		$this->assertFalse($coll->containsAll(array(self::NON_ITEM_STRING_2,self::ITEM_STRING_3)));
		$this->assertFalse($coll->containsAll(new Collection(array(self::NON_ITEM_STRING_2,self::ITEM_STRING_3))));
	}
	
	/**
	 * Test for equals
	 */
	public function testEquals()
	{
		$coll = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		
		$c = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		$this->assertTrue($coll->equals($c));
		
		$c->prop = true;
		$this->assertFalse($coll->equals($c));
		
		$coll->prop = false;
		$this->assertFalse($coll->equals($c));
		
		$coll->prop = true;
		$this->assertTrue($coll->equals($c));
		
		$this->assertFalse($coll->equals((array)$coll));
		$this->assertFalse($coll->equals(new Collection()));
		$this->assertFalse($coll->equals(array()));
	}
	
	/**
	 * Test for isEmpty
	 */
	public function testIsEmpty()
	{
		$coll = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		
		$c = new Collection();
		$this->assertTrue($c->isEmpty());
		$this->assertFalse($coll->isEmpty());
	}
	
	/**
	 * Test for removeAt
	 */
	public function testRemoveAt()
	{
		$coll = new Collection(array(self::ITEM_STRING_1, self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4));
		
		$c = clone $coll;
		$c->removeAt(2);
		$this->assertNotContains($coll[2], $c); // Index 2 no longer present
		$this->assertSame($c[2], $coll[3]); // Index 3 is now index 2 
	}
	
	/**
	 * Test for remove
	 */
	public function testRemove()
	{
		$coll = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3));
		
		$arg = self::ITEM_STRING_2;
		$coll->remove($arg);
		$this->assertNotContains($arg, $coll);
	}
	
	/**
	 * Test for removeAll
	 */
	public function testRemoveAll()
	{
		$coll = new Collection(array(self::ITEM_STRING_1, self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4, self::ITEM_STRING_5));
		
		$c = new Collection(array(self::ITEM_STRING_1, self::ITEM_STRING_3, self::ITEM_STRING_5));
		$coll->removeAll($c);
		$this->assertNotContains(self::ITEM_STRING_1, $coll);
		$this->assertContains(self::ITEM_STRING_2, $coll);
		$this->assertNotContains(self::ITEM_STRING_3, $coll);
		$this->assertContains(self::ITEM_STRING_4, $coll);
		$this->assertNotContains(self::ITEM_STRING_5, $coll);
	}
	
	/**
	 * Test for clear
	 */
	public function testClear()
	{
		$coll = new Collection(array(self::ITEM_STRING_1, self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4, self::ITEM_STRING_5));
		
		$coll->clear();
		$this->assertEquals(new Collection(), $coll);
	}
	
	/**
	 * Test for retainAll
	 */
	public function testRetainAllCollection()
	{
		$coll = new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4, self::ITEM_STRING_5));
		
		$c = new Collection(array(self::ITEM_STRING_1, self::ITEM_STRING_3, self::ITEM_STRING_5));
		
		$coll->retainAll($c);
		$this->assertEquals(new Collection(array_intersect($coll->getArrayCopy(), $c->getArrayCopy())), $coll);
	}
}

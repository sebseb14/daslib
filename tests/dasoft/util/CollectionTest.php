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

use Dasoft\Util\Collection;

/**
 * @category    Dasoft
 * @package     UnitTests
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
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
	
	public function providerTestTyped()
	{
		return array(
			array(null, 'integer', null, 123),
			array(null, 'integer', 'arg', 987),
		);
	}
	
	/**
	 * Test for Collection object creation with an array
	 * 
	 * @dataProvider providerTestTyped
	 */
	public function testTyped($array, $type, $key, $value)
	{
		$coll = new Collection($array, $type);
		if($key)
		{
			$coll->add($key, $value);
		}
		else
		{
			$coll->add($value);
		}
		$this->assertContains($value, $coll);
		if($key)
		{
			$this->assertEquals($value, $coll[$key]);
		}
	}
	
	public function providerTestCreationTypedException()
	{
		return array(
			array(null, 'integer', 123.456, 'InvalidArgumentException'),
			array(null, 'integer', '456', 'InvalidArgumentException'),
			array(null, 'integer', 'string', 'InvalidArgumentException'),
			array(array(123, 456, 'string'), 'integer', null, 'InvalidArgumentException'),
			array(array(123, 456, 'string'), new \Date(), 'string', 'InvalidArgumentException'),
		);
	}
	
	/**
	 * Test for Collection object creation with an array
	 * 
	 * @dataProvider providerTestCreationTypedException
	 */
	public function testCreationTypedException($array, $type, $value, $exception)
	{
		$this->setExpectedException($exception);
		$coll = new Collection($array, $type);
		$coll->add($value);
	}
	
	public function providerTestAdd()
	{
		return array(
			array(null, self::ITEM_STRING_1),
			array('arg', self::ITEM_STRING_2),
		);
	}
	
	/**
	 * Test for Add
	 * 
	 * @dataProvider providerTestAdd
	 */
	public function testAdd($key, $value)
	{
		$coll = new Collection();
		if($key)
		{
			$coll->add($key, $value);
		}
		else
		{
			$coll->add($value);
		}
		$this->assertContains($value, $coll);
		if($key)
		{
			$this->assertEquals($value, $coll[$key]);
		}
	}
	
	public function testAddException()
	{
		$coll = new Collection(null, null, null, true);
		
		$this->setExpectedException('ErrorException', 'requires both index and item arguments');
		$coll->add(123);
	}
	
	public function providerTestAddAll()
	{
		return array(
			array(
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				array(self::ITEM_STRING_2, self::ITEM_STRING_3)
			),
			array(
				new Collection(array(self::ITEM_STRING_2, self::ITEM_STRING_3)),
				array(self::ITEM_STRING_2, self::ITEM_STRING_3)
			),
		);
	}
	
	/**
	 * Test for addAll with a Collection
	 * 
	 * @dataProvider providerTestAddAll
	 */
	public function testAddAll($items, $expected)
	{
		$coll = new Collection();
		// Add Collection
		$coll->addAll($items);
		foreach($expected as $item)
		{
			$this->assertContains($item, $coll);
			$this->assertContains($item, $coll);
		}
	}
	
	public function providerTestAddAllException()
	{
		return array(
			array(null, null, null, null, 'addAll', new \Date(), 'ErrorException', 'must implement Traversable or be an array'),
			array(null, null, null, true, 'addAll', array('onlyvalue'), 'ErrorException', 'requires items to be indexed'),
			array(null, null, 3, null, 'addAll', array('1','2','3','4'), 'OverflowException', 'Attempting to access an item beyond the capacity of the collection'),
			array(null, null, null, null, 'retainAll', new \Date(), 'ErrorException', 'must implement Traversable or be an array'),
		);
	}
	
	/**
	 * Test for addAll exception
	 * 
	 * @dataProvider providerTestAddAllException
	 */
	public function testMethodAllException($array, $type, $capacity, $enforcePropName, $method, $arg, $exception, $message)
	{
		$coll = new Collection($array, $type, $capacity, $enforcePropName);
		
		$this->setExpectedException($exception, $message);
		$coll->{$method}($arg);
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
	
	public function providerTestEquals()
	{
		return array(
			array(
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				'one','one',
				true, true
			),
			array(
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				'one','two',
				true, false
			),
			array(
				array(self::ITEM_STRING_1, self::ITEM_STRING_2),
				array(self::ITEM_STRING_2, self::ITEM_STRING_3),
				'one','one',
				false, false
			),
		);
	}
	
	/**
	 * Test for equals
	 * 
	 * @dataProvider providerTestEquals
	 */
	public function testEquals($array1, $array2, $prop1, $prop2, $expected1, $expected2)
	{
		$coll1 = new Collection($array1);
		$coll2 = new Collection($array2);
		$this->assertEquals($expected1, $coll1->equals($coll2));
		
		$coll1->prop = $prop1;
		$coll2->prop = $prop2;
		$this->assertEquals($expected2, $coll1->equals($coll2));
		
		$this->assertFalse($coll1->equals((array)$coll1));
		$this->assertFalse($coll1->equals(new Collection()));
		$this->assertFalse($coll1->equals(array()));
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
	
	public function providerTestRemoveAt()
	{
		return array(
			array(
				array(self::ITEM_STRING_1, self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4),
				2, self::ITEM_STRING_3
			),
			array(
				array('arg0' => self::ITEM_STRING_1, 'arg1' => self::ITEM_STRING_2, 'arg2' => self::ITEM_STRING_3, 'arg3' => self::ITEM_STRING_4),
				'arg2', self::ITEM_STRING_3
			),
		);
	}
	
	/**
	 * Test for removeAt
	 * 
	 * @dataProvider providerTestRemoveAt
	 */
	public function testRemoveAt($array, $index, $value)
	{
		$c = new Collection($array);
		$c->removeAt($index);
		$this->assertNotContains($value, $c);
		if(is_string($index))
		{
			$this->assertArrayNotHasKey($index, $c->getArrayCopy());
		}
		elseif(is_int($index))
		{
			$this->assertSame($c[$index], $array[$index+1]);
		}
	}
	
	public function providerTestRemove()
	{
		return array(
			array(
				array(self::ITEM_STRING_1, self::ITEM_STRING_2, self::ITEM_STRING_3, self::ITEM_STRING_4),
				self::ITEM_STRING_3
			),
			array(
				array('arg0' => self::ITEM_STRING_1, 'arg1' => self::ITEM_STRING_2, 'arg2' => self::ITEM_STRING_3, 'arg3' => self::ITEM_STRING_4),
				self::ITEM_STRING_3
			),
		);
	}
	
	/**
	 * Test for remove
	 * 
	 * @dataProvider providerTestRemove
	 */
	public function testRemove($array, $value)
	{
		$coll = new Collection($array);
		
		$coll->remove($value);
		$this->assertNotContains($value, $coll);
		$this->assertFalse($coll->remove(self::NON_ITEM_STRING_1));
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
	
	/**
	 * Test conversion methods
	 */
	public function testConversion()
	{
		$array = array('two', 'three');
		$coll = new Collection($array);
		$this->assertEquals($array, $coll->toArray());
		$this->assertEquals('two three', $coll->toString());
	}
}

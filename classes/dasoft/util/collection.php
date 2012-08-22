<?php
/**
 * Dasoft Toolkit
 * 
 * @category   Dasoft
 * @package    Dasoft\Util\Collection
 * @author     Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright  Copyright (c) 2010-2012 Daniel Arsenault
 * @license    BSD License
 * @version    $Id$-expansion
 */

namespace Dasoft\Util;

use OverflowException, InvalidArgumentException;

/**
 * Generic collection class
 * 
 * @category   Dasoft
 * @package    Dasoft\Util\Collection
 * @author     Daniel Arsenault <daniel.arsenault@dasoft.ca>
 */
class Collection extends \ArrayObject //implements \IteratorAggregate , \ArrayAccess , \Serializable , \Countable
{
	// {{{ Constants
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Statis Properties
	
	// {{{ Static Methods
	// }}} End Static Methods
	
	// {{{ Properties
	/**
	 * The type of objects the collection is accepting
	 * @var string
	 */
	protected $_type;
	
	/**
	 * The number of items that the collection can contain
	 * @var int
	 */
	protected $_capacity;
	
	/**
	 * Whether to enfore key/value pair
	 * @var bool
	 */
	protected $_enforcePropName;
	// }}} End Properties
	
	// {{{ Methods
	/**
	 * Collection constructor
	 * 
	 * @param mixed[]   $array      The elements to initialize the collection with
	 * @param string    $type       The type of the collection elements
	 * @param integer   $capacity   The maximum capacity of the collection
	 */
	public function __construct($array = null, $type = null, $capacity = null, $enforcePropName = null)
	{
		$this->_type = (is_null($type)
						? null
						: (is_string($type)
							? $type
							: (is_object($type)
								? get_class($type)
								: gettype($type))));
		$this->_capacity = $capacity;
		$this->_enforcePropName = $enforcePropName;
		
		$array = is_null($array) ? array() : $array;
		if($this->_type)
		{
			parent::__construct();
			$this->addAll($array);
		}
		else
		{
			parent::__construct($array);
		}
	}
	
	/**
	 * Add the specified item to the collection
	 * 
	 * @param mixed $index
	 * @param mixed $item
	 * @return bool
	 */
	public function add($index, $item = null)
	{
		if(is_null($item))
		{
			if($this->_enforcePropName === true)
			{
				$trace = debug_backtrace();
				throw new \ErrorException(get_called_class() . "::add requires both index and item arguments.", 0, 0, $trace[0]['file'], $trace[0]['line']);
			}
			$item = $index;
			$this->append($item);
		}
		else 
		{
			$this->offsetSet($index, $item);
		}
		
		return true;
	}
	
	/**
	 * Add all of the items from $items to the collection
	 * 
	 * @param mixed $items
	 * @return bool
	 */
	public function addAll($items)
	{
		if(!($items instanceof \Traversable) && !is_array($items))
		{
			$trace = debug_backtrace();
			throw new \ErrorException("Argument 1 passed to " . __METHOD__ . " must implement Traversable or be an array, " . get_class($items) . " given.", 0, 0, $trace[0]['file'], $trace[0]['line']);
		}
		
		// Check if $items is a 0-based array
		if($items === array_values((array) $items))
		{
			if($this->_enforcePropName === true)
			{
				$trace = debug_backtrace();
				throw new \ErrorException(get_called_class() . "::addAll requires items to be indexed.", 0, 0, $trace[0]['file'], $trace[0]['line']);
			}
			
			foreach ($items as $item)
			{
				$this->add($item);
			}			
		}
		else 
		{
			foreach ($items as $index => $item)
			{
				$this->add($index, $item);
			}
		}
		
		
		return true;
	}
	
	/**
	 * Remove all of the items from the collection
	 * 
	 * @return void
	 */
	public function clear()
	{
		$this->exchangeArray(array());
	}
	
	/**
	 * Tells whether the collection contains the specified item
	 * 
	 * @param mixed $item
	 * @return bool
	 */
	public function contains($item)
	{
		return in_array($item, (array)$this);
	}
	
	/**
	 * Tells whether the collection contains all of the items from $items
	 * 
	 * @param mixed $item
	 * @return bool
	 */
	public function containsAll($items)
	{
		return ((array)$items == array_intersect((array)$items, (array)$this));
	}
	
	/**
	 * Compares the specified object with the collection for equality
	 * 
	 * @param mixed $object
	 * @return bool
	 */
	public function equals($object)
	{
		// We test both object-wise and array-wise
		return ($object == $this && (array)$object == (array)$this);
	}
	
	/**
	 * Tells whether the collection is empty
	 * 
	 * @return bool
	 */
	public function isEmpty()
	{
		return ($this->count() == 0);
	}
	
	/**
	 * Removes an instance of the specified item from the collection
	 * 
	 * @param mixed $item
	 * @return bool
	 */
	public function remove($item)
	{
		$index = array_search($item, (array)$this);
		if($index !== false)
		{
			return $this->removeAt($index);
		}
		
		return false;
	}
	
	/**
	 * Removes the item at $index from the collection
	 * 
	 * @param mixed $index
	 * @return bool
	 */
	public function removeAt($index)
	{
		$array = (Array)$this;
		
		if(!is_numeric($index))
		{
			unset($array[$index]);
		}
		else
		{
			array_splice($array, $index, 1);
		}
		
		$this->exchangeArray($array);
		
		return true;
	}
	
	/**
	 * Removes all the specified items from the collection
	 * 
	 * @param mixed $items
	 * @return bool
	 */
	public function removeAll($items)
	{
		$affected = false;
		foreach ($items as $item)
		{
			$affected |= $this->remove($item);
		}
		
		return $affected;
	}
	
	/**
	 * Retains only the items from the collection that are present in $items
	 * 
	 * @param mixed $items
	 * @return bool
	 */
	public function retainAll($items)
	{
		if(!($items instanceof \Traversable) && !is_array($items))
		{
			$trace = debug_backtrace();
			throw new \ErrorException("Argument 1 passed to " . __METHOD__ . " must implement Traversable or be an array, " . get_class($items) . " given.", 0, 0, $trace[0]['file'], $trace[0]['line']);
		}
		
		$removeList = array();
		$affected = false;
		foreach ($this as $item)
		{
			if(!in_array($item, (array)$items))
			{
				$removeList[] = $item;
			}
		}
		
		foreach ($removeList as $item)
		{
			$affected |= $this->remove($item);
		}
		
		return $affected;
	}
	
	/**
	 * Overload "setter" to implement type and capacity validation
	 * 
	 * @throws OverflowException
	 * @throws InvalidArgumentException
	 * @see ArrayObject::offsetSet()
	 */
	public function offsetSet($index, $item)
	{
		// Type checking
		$isTypeFunc = function($type, $item){
			$func = "is_{$type}";
			if(function_exists($func))
			{
				return $func($item);
			}
			else 
			{
				return $item instanceof $type;
			}
		};
		if(!is_null($this->_type) && !is_null($item) && !($isTypeFunc($this->_type, $item)))
		{
			throw new InvalidArgumentException("Invalid argument type, expecting {$this->_type} and found " . (is_object($item) ? get_class($item) : gettype($item)));
		}
		
		// Capacity checking
		if(!is_null($this->_capacity)
			&& ((is_numeric($index) && $index >= $this->_capacity)
				|| (!is_numeric($index) && !@isset($this[$index]) && $this->count() >= $this->_capacity)
				|| (is_null($index) && $this->count() >= $this->_capacity)))
		{
			throw new OverflowException("Attempting to access an item beyond the capacity of the collection: {$this->_capacity}");
		}
		echo "$index:$item\n";
		parent::offsetSet($index, $item);
	}
	
	/**
	 * Returns an array containing all of the items in the collection
	 * 
	 * @return mixed[]
	 */
	public function toArray()
	{
		return (array)$this;
	}
	
	/**
	 * Returns an string representation of the collection
	 * 
	 * @return mixed[]
	 */
	public function toString()
	{
		return $this->__toString();
	}
	
	public function __toString()
	{
		return implode(' ', (array)$this);
	}	
}
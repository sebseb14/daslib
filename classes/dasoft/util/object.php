<?php
/**
 * Dasoft Toolkit
 *
 * @category   Dasoft
 * @package    Dasoft\Util\Object
 * @author     Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright  Copyright (c) 2010-2012 Daniel Arsenault
 * @license    BSD License
 * @version    $Id$-expansion
 */

namespace Dasoft\Util;

use ErrorException;

/**
 * Generic Object class
 *
 * @category   Dasoft
 * @package    Dasoft\Util\Object
 * @author     Daniel Arsenault <daniel.arsenault@dasoft.ca>
 */
abstract class Object
{
	// {{{ Constants
	const MODE_STRICT = 1;
	const MODE_FLEXIBLE = 2;
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Static Properties
	
	// {{{ Static Methods
	// }}} End Static Methods
	
	// {{{ Properties
	/**
	 * The strickness mode under which the object is operating
	 * @var integer
	 */
	private $_object_mode;
	
	/**
	 * Stores the object data properties accessible throught magic accessors
	 * @var mixed
	 */
	protected $_object_properties = array();
	// }}} End Properties
	
	// {{{ Methods
	public function __construct($properties = array(), $mode = self::MODE_STRICT)
	{
		$this->_object_mode = $mode;
		$this->setProperties($properties);
	}
	
	/**
	 * Get the properties
	 *
	 * @return array
	 */
	public function getProperties($name = null, $ignoreNulls = false)
	{
		if(is_null($name) || empty($name))
		{
			if($ignoreNulls)
			{
				$return = array();
				foreach ($this->_object_properties as $name => $value)
				{
					if(!is_null($value))
					{
						$return[$name] = $value;
					}
				}
				return $return;
			}
				
			return $this->_object_properties;
		}
		elseif(is_array($name))
		{
			$return = array();
			foreach ($name as $n)
			{
				if(isset($this->_object_properties[$n]))
				{
					$return[$n] = $this->_object_properties[$n];
				}
				else
				{
					if(!$ignoreNulls)
					{
						$return[$n] = null;
					}
				}
			}
				
			return $return;
		}
		elseif(isset($this->_object_properties[$name]))
		{
			return $this->_object_properties[$name];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Set the properties
	 *
	 * @param array $post
	 */
	public function setProperties(array $properties)
	{
		//echo get_called_class();
		foreach ($properties as $key => $value)
		{
			//echo "\n{$key}:{$value}\n";
			$this->{$key} = $value;
		}
	}
	// }}} End Methods
	
	// {{{ Magic Methods
	/**
	 * Magig setter
	 *
	 * This method should normally not be called directly
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws ErrorException
	 */
	public function __set($name, $value)
	{
		// Filter strings
		if(is_string($value))
		{
			$value = trim($value);
			if($value === ""){
				$value = null;
			}
		}
	
		// Detect "real" private
		if(property_exists($this, $name)
				&& strpos($name,'_') === 0)
		{
			$refl = new ReflectionProperty($this, $name);
			$visibility = $refl->isProtected() ? 'protected' : ($refl->isPrivate() ? 'private' : '');
			$trace = debug_backtrace();
			throw new ErrorException("Cannot access {$visibility} property ".get_class($this)."::\$$name", 1, 0, $trace[0]['file'] , $trace[0]['line']);
		}
	
		// Use "property setters" when available
		$propSetter = __FUNCTION__ . '_' . $name;
		if(method_exists($this, $propSetter))
		{
			$this->{$propSetter}($value);
		}
		// Or object properties
		elseif(key_exists($name, $this->_object_properties))
		{
			$this->_object_properties[$name] = $value;
		}
		// Or direct access
		elseif(property_exists($this, $name))
		{
			$this->{$name} = $value;
		}
		elseif($this->_object_mode == self::MODE_FLEXIBLE)
		{
			$this->_object_properties[$name] = $value;
		}
		else
		{
			$trace = debug_backtrace();
			throw new ErrorException("Undefined property " . get_called_class() . "::\$$name", 1, 0, $trace[0]['file'] , $trace[0]['line']);
		}
	}
	
	/**
	 * Magig getter
	 *
	 * This method should normally not be called directly
	 *
	 * @param string $name
	 * @throws ErrorException
	 * @return mixed
	 */
	public function __get($name)
	{
		// Detect "real" private
		if(property_exists($this, $name)
				&& strpos($name,'_') === 0)
		{
			$refl = new ReflectionProperty($this, $name);
			$visibility = $refl->isProtected() ? 'protected' : ($refl->isPrivate() ? 'private' : '');
			$trace = debug_backtrace();
			throw new ErrorException("Cannot access {$visibility} property ".get_class($this)."::\$$name", 1, 0, $trace[0]['file'] , $trace[0]['line']);
		}
	
		// Use "property setters" when available
		$propGetter = __FUNCTION__ . '_' . $name;
		if(method_exists($this, $propGetter))
		{
			return $this->{$propGetter}();
		}
		// Or object properties
		elseif(key_exists($name, $this->_object_properties))
		{
			return $this->_object_properties[$name];
		}
		// Or direct access
		elseif(property_exists($this, $name))
		{
			return $this->{$name};
		}
		elseif($this->_object_mode == self::MODE_FLEXIBLE)
		{
			return null;
		}
		else
		{
			$trace = debug_backtrace();
			throw new ErrorException("Undefined property " . get_called_class() . "::\$$name", 1, 0, $trace[0]['file'] , $trace[0]['line']);
		}
	}
	// }}} End Magic Methods
}
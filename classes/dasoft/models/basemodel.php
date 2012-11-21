<?php
/**
 * Dasoft Toolkit
 *  
 * @category	Dasoft
 * @package		Dasoft\Models
 * @author		Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @version		$Id: basemodel.php 50 2012-04-24 20:37:02Z darsenault $
 */

namespace Dasoft\Models;

use ErrorException, Validation, ReflectionProperty;

/**
 * BaseModel
 * 
 * @category    Dasoft
 * @package     Dasoft\Models
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @property    mixed errors
 */
abstract class BaseModel
{
	// {{{ Constants
	const VALIDATION_RULES_DEFAULT = null;
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Static Properties
	
 	// {{{ Static Methods
	// }}} End Static Methods

	// {{{ Properties
	/**
	 * Stores the object data properties accessible throught magic accessors
	 * @var mixed
	 */
	protected $_properties = array();
	
	/**
	* @var Validation
	*/
	protected $_validation;
	
	protected $_validationRules = array();
	// }}} End Properties
	
	// {{{ Methods
	public function __construct($properties = array())
	{
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
				foreach ($this->_properties as $name => $value)
				{
					if(!is_null($value))
					{
						$return[$name] = $value;
					}
				}
				return $return;
			}
			
			return $this->_properties;
		}
		elseif(is_array($name))
		{
			$return = array();
			foreach ($name as $n)
			{
				if(isset($this->_properties[$n]))
				{
					$return[$n] = $this->_properties[$n];
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
		elseif(isset($this->_properties[$name]))
		{
			return $this->_properties[$name];
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
	
	public function getSerializableProperties($exclude = array(), $ignoreNulls = true)
	{
		$return = array();
		foreach (array_keys($this->getProperties()) as $name)
		{
			if(in_array($name, $exclude)){ continue; }
			
			if(method_exists($this, "getSerializable{$name}Property"))
			{
				$value = $this->{"getSerializable{$name}Property"}();
			}
			elseif(is_object($this->{$name}) && method_exists($this->{$name}, __FUNCTION__))
			{
				$value = $this->{$name}->{__FUNCTION__}();
			}
			else
			{
				$value = $this->{$name};
			}
			
			if(!is_null($value) || $ignoreNulls == false)
			{
				$return[$name] = $value;
			}
		}
		
		return $return;
	}
	
	/**
	 * Validate the model
	 * 
	 * @param mixed $rules
	 */
	public function validate($rules = self::VALIDATION_RULES_DEFAULT)
	{
		if(is_null($rules))
		{
			$rules = static::VALIDATION_RULES_DEFAULT;
		}
		
		if(is_string($rules))
		{
			$rules = $this->_validationRules[$rules];
		}
		$this->_validation = Validation::factory($this->getProperties());
		foreach ($rules as $field => $rule)
		{
			$this->_validation->rules($field, $rule);
		}
	
		return $this->_validation->check();
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
			if($value === ""){ $value = null; }
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
		elseif(key_exists($name, $this->_properties))
		{
			$this->_properties[$name] = $value;
		}
		// Or direct access
		elseif(property_exists($this, $name))
		{
			$this->{$name} = $value;
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
		elseif(key_exists($name, $this->_properties))
		{
			return $this->_properties[$name];
		}
		// Or direct access
		elseif(property_exists($this, $name))
		{
			return $this->{$name};
		}
		else 
		{
			$trace = debug_backtrace();
			throw new ErrorException("Undefined property " . get_called_class() . "::\$$name", 1, 0, $trace[0]['file'] , $trace[0]['line']);
		}
	}
	
	/**
	* @var mixed errors
	*/
	protected function __get_errors()
	{
		return ( $this->_validation ? $this->_validation->errors('errors') : array());
	}
	// }}} End Magic Methods
}
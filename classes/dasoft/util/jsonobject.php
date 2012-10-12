<?php
/**
 * Dasoft Toolkit
 * 
 * @category     Dasoft
 * @package      Dasoft\Util\JsonObject
 * @author       Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright    Copyright (c) 2010-2012 Daniel Arsenault
 * @license      BSD License
 * @version      $Id$-expansion
 */

namespace Dasoft\Util;

use ArrayObject;

/**
 * JsonObject class
 * 
 * The json object is a dynamic object (akin to stdClass) that, like in json notation,
 * can be seen as an array or an object. It can be easily created from a json string
 * or be convert to a json string.
 *
 * @category     Dasoft
 * @package      Dasoft\Util\JsonObject
 * @author       Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @license      BSD License
 */
class JsonObject extends ArrayObject
{
	// {{{ Constants
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Static Properties
	
	// {{{ Static Methods
	// }}} End Static Methods
	
	// {{{ Properties
	protected $_init = array();
	// }}} End Properties
	
	// {{{ Methods
	public function __construct($input = array())
	{
		if(is_string($input))
		{
			$input = json_decode($input);
		}
		if(is_null($input)){ $input = array(); }
		
		$array = array_merge($this->_init, (array)$input);
		
		parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
	}
	
	public function offsetGet($index)
	{
		return $this->offsetExists($index) ? parent::offsetGet($index) : null;
	}
	// }}} End Methods
	
	// {{{ Magic Methods
	public final function __toString()
	{
		return json_encode($this->getArrayCopy());
	}
	// }}} End Magic Methods
}
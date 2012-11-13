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

/**
 * Json collection class
 * 
 * @category   Dasoft
 * @package    Dasoft\Util\JsonCollection
 * @author     Daniel Arsenault <daniel.arsenault@dasoft.ca>
 */
class JsonCollection extends Collection
{
	// {{{ Constants
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Statis Properties
	
	// {{{ Static Methods
	// }}} End Static Methods
	
	// {{{ Properties
	// }}} End Properties
	
	// {{{ Methods
	
	public function __toString()
	{
		return json_encode((array)$this);
	}	
}
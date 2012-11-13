<?php
/**
 * Dasoft Toolkit
 * 
 * @category     Dasoft
 * @package      Dasoft\Util
 * @author       Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @copyright    Copyright (c) 2010-2012 Daniel Arsenault
 * @license      BSD License
 * @version      $Id$-expansion
 */

namespace Dasoft\Util;

/**
 * Date class
 * 
 * A simple DateTime with added string conversion mechanism
 *
 * @category     Dasoft
 * @package      Dasoft\Util\DateTime
 * @author       Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @license      BSD License
 */
class DateTime extends \DateTime
{
	// {{{ Constants
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Static Properties
	
	// {{{ Static Methods
	// }}} End Static Methods
	
	// {{{ Properties
	public $stringFormat = self::ISO8601;
	// }}} End Properties
	
	// {{{ Methods
	// }}} End Methods
	
	// {{{ Magic Methods
	public final function __toString()
	{
		return $this->format($this->stringFormat);
	}
	// }}} End Magic Methods
}
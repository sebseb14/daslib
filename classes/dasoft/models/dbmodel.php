<?php
/**
 * Dasoft Toolkit
 *  
 * @category	Dasoft
 * @package		Dasoft\Models
 * @author		Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @version		$Id: dbmodel.php 20 2011-07-18 02:46:09Z darsenault $
 */

namespace Dasoft\Models;

use Arr, DB, InvalidArgumentException, Validation_Exception, Database_Exception;

/**
 * DbModel
 * 
 * @category    Dasoft
 * @package     Dasoft\Models
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 */
abstract class DbModel extends BaseModel
{
	// {{{ Constants
	// }}} End Constants
	
	/**@#+
	 * Static Members
	 */
	// {{{ Static Properties
	// }}} End Static Properties
	
 	// {{{ Static Methods
 	/**
	 * Fetch a list of Models
	 * 
	 * @param    mixed   filter    Filter is an array of filter, each being in the form of ['left operand','operator','right operand']
	 * @param    mixed   order     Order is an array of order, each being in the form of ['identifier','direction']
	 * @return   BaseModel[]
	 */
	public static function fetchList($filter = array(), $order = array())
	{
		$instance = new static();
		
		$query = DB::select()->from($instance->_tblName);
		
		// Apply user filter
		if(is_null($filter)) { $filter = array(); }
		foreach ($filter as $f)
		{
			if(count($f) !== 3)
			{
				$trace = debug_backtrace();
				throw new ErrorException("Invalid argument 1 in  ".__METHOD__, 1, 0, $trace[0]['file'] , $trace[0]['line']);
			}
			
			$query->and_where($f[0], $f[1], $f[2]);
		}
		
		foreach ($order as $o)
		{
			if(count($o) !== 2)
			{
				$trace = debug_backtrace();
				throw new ErrorException("Invalid argument 2 in  ".__METHOD__, 1, 0, $trace[0]['file'] , $trace[0]['line']);
			}
			$query->order_by($o[0],$o[1]);
		}
		
		$results = $query->execute();
		
		$modelList = array();
		foreach ($results as $r)
		{
			$model = new static($results[0]);
			
			$model->_fetchHash = md5(serialize($model->getSerializableProperties()));
			
			array_push($modelList, $model);
		}
		
		return $modelList;
	}
	// }}} End Static Methods
	/**@#-*/
	
	// {{{ Properties
	/**
	 * @var string    The table that will store instances of the model. Defaults to the class name -without namespace.
	 */
	protected $_tblName;
	
	/**
	 * @var string    The name of the primary key. Defaults to LCFIRST(tblName) . Id
	 */
	protected $_pkName;
	
	/**
	 * @var string    The hash of the last fetched results.
	 */
	protected $_fetchHash;
	
	protected $_serializeExclusion = array(
		'_serializeExclusion','_tblName','_pkName','_fetchHash','_validation', '_validationRules'
	);
	// }}} End Properties
	
	// {{{ Methods
	public function __construct($properties = array())
	{
		parent::__construct($properties);
		
		if(!$this->_tblName)
		{
			$this->_tblName = strrpos(get_called_class(), '\\') === false ? get_called_class() : substr(get_called_class(), strrpos(get_called_class(), '\\')+1);
		}
		if(!$this->_pkName)
		{
			$this->_pkName = lcfirst(str_replace('`', '', "{$this->_tblName}Id"));
		}
	}
	
	public function fetch($validation = null, $strict = false)
	{
		/*
		 * We do lazy fetching, to force refetch, call invalidate()
		 */ 
		if($this->_fetchHash){ return true; }
		
		if(is_null($validation)){ $validation = __FUNCTION__; }
		if(is_string($validation)){ $validation = Arr::get($this->_validationRules, $validation, null); }
		if(!$validation){ throw new InvalidArgumentException("Invalid validation", null, null); }
		if(!$this->validate($validation))
		{
			throw new Validation_Exception($this->_validation, 'Failed to validate ' . json_encode($this->errors));
		}
		
		$query = DB::select()->from($this->_tblName)->as_assoc();
		
		$properties = ($strict ? $this->getProperties(null, true) : $this->getProperties(array_keys($validation), true));
		foreach ($properties as $k => $v)
		{
			$query->and_where($k, '=', $v);
		}
		
		$result = $query->execute();
		
		if($result->count())
		{
			$this->setProperties($result[0]);
			
			$this->_fetchHash = md5(serialize($this->getSerializableProperties()));
		}
		
		return (bool)$result->count();
	}
	
	public function save($fields = null, $exclude = false)
	{
		try
		{
			return $this->insert();
		}
		catch(Database_Exception $e)
		{
			if($e->getCode() == '23000') // @fixme MySQL Specific
			{
				return $this->update($fields = null, $exclude = false);
			}
		}
	}
	
	public function insert()
	{
		if(isset($this->_validationRules['insert']) && !$this->validate('insert'))
		{
			throw new Validation_Exception($this->_validation, 'Failed to validate ' . json_encode($this->errors));
		}
		
		$properties = $this->getProperties(null, true);
		
		$result = DB::insert($this->_tblName, array_keys($properties))->values($properties)->execute();
		
		return (bool)$result[1];
	}
	
	public function update($fields = null, $exclude = false)
	{
		if($exclude){ $fields = array_diff(array_keys($this->getProperties()), $fields); }
		$properties = $this->getProperties($fields);
		
		$result = DB::update($this->_tblName)->set($properties)->where($this->_pkName,'=',$this->{$this->_pkName})->execute();
		
		return is_int($result);
	}
	// }}} End Methods
	
	// {{{ Getters/Setters Methods
	protected function &__get_id()
	{
		return $this->{$this->_pkName};
	}
	
	protected function __set_id($value)
	{
		$this->{$this->_pkName} = $value;
	}
	// }}} End Getters/Setters Methods
}
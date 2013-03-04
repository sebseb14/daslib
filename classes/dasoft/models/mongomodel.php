<?php
/**
 *  Dasoft Toolkit
 *  
 * @category	Dasoft
 * @package		Dasoft\Models
 * @author		Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * @version		$Id: mongomodel.php 33 2011-08-27 01:25:58Z darsenault $
 */

namespace Dasoft\Models;

use Dasoft\Database\Mongo, MongoId, MongoDBRef, MongoDate, Arr;
use InvalidArgumentException, Validation_Exception;

/**
 * BaseModel
 * 
 * @category    Dasoft
 * @package     Dasoft\Models
 * @author      Daniel Arsenault <daniel.arsenault@dasoft.ca>
 * 
 * @property    String    id
 */
abstract class MongoModel extends BaseModel
{
	// {{{ Constants
	const VALIDATION_RULES_DEFAULT = self::VALIDATION_RULES_INSERT;
	const VALIDATION_RULES_INSERT = 'insert';
	const VALIDATION_RULES_UPDATE = 'update';
	
	const VALID_ID_LENGTH = 24;
	// }}} End Constants
	
	// {{{ Static Properties
	// }}} End Static Properties
	
 	// {{{ Static Methods
 	/**
	 * Fetch a list of Models
	 * 
	 * @param    mixed   filter
	 * @param    mixed   order
	 * @return   MongoModel[]
	 */
	public static function fetchList(array $filter = null, array $sort = null, array $fields = null, $limit = null)
	{
		// {{{ Preconditions
		// }}} End Preconditions
		$instance = new static();
		if(is_null($filter)){ $filter = array(); }
		if(is_null($fields)){ $fields = array(); }
		$cursor = Mongo::instance()
						->{$instance->_collectionName}
						->find($filter, $fields);
		if($sort)
		{
			// Translate SQL direction to MongoDB
			foreach ($sort as &$v)
			{
				if(strtoupper($v) == 'ASC'){ $v = 1; }
				if(strtoupper($v) == 'DESC'){ $v = -1; }
			}
			
			$cursor->sort($sort);
		}
		if(!is_null($limit))
		{
			$cursor->limit($limit);
		}
		
		$modelList = array();
		foreach ($cursor as $model)
		{
			$modelList[] = new static($model);
		}
		
		return $modelList;
	}
	
	public static function count(array $filter = null)
	{
		// {{{ Preconditions
		// }}} End Preconditions
		
		$instance = new static();
		if(is_null($filter)){
			$filter = array();
		}
		
		$cursor = Mongo::instance()
			->{$instance->_collectionName}
			->count($filter);
		
		return $cursor;
	}
	// }}} End Static Methods
	
	// {{{ Properties
	protected $_references = array();
	protected $_dereferences = array();
	
	/**
	 * @var string    The collection that will store instances of the model. Defaults to the class name -without namespace.
	 */
	protected $_collectionName;
	
	/**
	 * @var string    The hash of the last fetched results.
	 */
	protected $_fetchHash;
	
	protected $_serializeExclusion = array(
			'_serializeExclusion','_collectionName','_fetchHash','_validation', '_validationRules'
	);
	// }}} End Properties
	
	// {{{ Methods
	public function __construct($properties = array())
	{
		parent::__construct($properties);
		if(!$this->_collectionName)
		{
			$className = get_called_class();
			$parts = explode('\\', $className);
		
			// Guess Model Collection from class name
			$dbCollection = array_pop($parts);
			$this->_collectionName = $dbCollection;
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
		
		$filter = ($strict ? $this->getProperties(null, true) : $this->getProperties(array_keys($validation), true));
		
		$model = Mongo::instance()
						->{$this->_collectionName}
						->findOne($filter);
		
		if($model)
		{
			$this->setProperties($model);
			$this->_fetchHash = md5(serialize($this->getSerializableProperties()));
			return true;
		}
		
		return false;
	}
	
	protected function _dereference(array $ref)
	{
		return Mongo::instance()
			->{$this->_collectionName}
			->getDBRef($ref);
	}
	
	public function save()
	{
		// {{{ Preconditions
		// }}} End Preconditions
		
		if(key_exists('_id', $this->_properties) && empty($this->_id))
		{
			$this->_id = new MongoId();
		}
	
		$properties = $this->getProperties();
		foreach($properties as $name => &$prop)
		{
			if(method_exists($prop, 'getProperties'))
			{
				$prop = $prop->getProperties();
			}
		}
	
		$result = Mongo::instance()
			->{$this->_collectionName}
			->save($properties, array('safe'=>true));
		
		return (bool)$result['ok'];
	}
	
	public function insert()
	{
		// {{{ Preconditions
		// }}} End Preconditions
		
		if(!$this->_id)
		{
			$this->_id = new MongoId();
		}
		
		$properties = $this->getProperties();
		foreach($properties as $name => &$prop)
		{
			if(method_exists($prop, 'getProperties'))
			{
				$prop = $prop->getProperties();
			}
		}
		
		$result = Mongo::instance()
						->{$this->_collectionName}
						->insert($properties, array('safe'=>true));
		
		return (bool)@$result['ok'];
	}
	
	public function update($filter = array("_id"))
	{
		// {{{ Preconditions
		if(!$filter)
		{
			$filter = array("_id");
		}
		// }}} End Preconditions
		
		$properties = $this->getProperties();
		foreach($properties as $name => &$prop)
		{
			if(method_exists($prop, 'getProperties'))
			{
				$prop = $prop->getProperties();
			}
		}
		
		$filters = array();
		foreach ($filter as $fKey => $fVal)
		{
			if(is_numeric($fKey))
			{
				$filters[$fVal] = $properties[$fVal];
			}
			else
			{
				$filters[$fKey] = $fVal;
			}
		}
		
		$result = Mongo::instance()
						->{$this->_collectionName}
						->update($filters, $properties, array('safe'=>true, 'upsert' => false));
		
		return $result['updatedExisting'];
	}
	
	
	public function delete($filter = array("_id"))
	{
		// {{{ Preconditions
		if(!$filter)
		{
			$filter = array("_id");
		}
		// }}} End Preconditions
		
		$properties = $this->getProperties();
		$filters = array();
		foreach ($filter as $fKey => $fVal)
		{
			if(is_numeric($fKey))
			{
				$filters[$fVal] = $properties[$fVal];
			}
			else
			{
				$filters[$fKey] = $fVal;
			}
		}
		
		$result = Mongo::instance()->{static::dbName()}
						->{static::dbCollection()}
						->remove($filters, array('safe'=>true, 'justOne' => true));
		
		return $result['ok'];
	}
	
	//abstract public function toJson();
	// }}} End Methods
	
	// {{{ Getters/Setters
	public function __set($name, $value)
	{
		// @todo clean dereferences if conflicting
		if($value instanceof MongoModel)
		{
			$this->_dereferences[$name] = $value;
		}
		
		if(key_exists($name, $this->_references))
		{
			if (is_null($this->_references[$name]))
			{
				// We expect to work with a mongo reference
				if($value instanceof MongoModel)
				{
					$value = Mongo::instance()->createDBRef($value->_collectionName, $value->_id);
				}
				 
				if(!MongoDBRef::isRef($value))
				{
					throw new InvalidArgumentException('@todo: Set proper excetion for MongoModel::__set() bad mongo reference');
				}
			}
			else
			{
				// We expect to work with a manual reference thus mongo id
				if($value instanceof MongoModel)
				{
					$value = $value->_id;
				}
				if(is_string($value))
				{
					$value = new MongoId($value);
				}
				
				if(!($value instanceof MongoId))
				{
					throw new InvalidArgumentException('@todo: Set proper excetion for MongoModel::__set() bad manual reference');
				}
			}
		}
		
		parent::__set($name, $value);
	}
	
	public function __get($name)
	{
		if(key_exists($name, $this->_references))
		{
			// @todo check dereferences for conflicting
			if(@$this->_dereferences[$name] instanceof MongoModel)
			{
				$value = $this->_dereferences[$name];
			}
			else 
			{
				$dbref = !is_null($this->_references[$name])
							? Mongo::instance()->createDBRef($this->_references[$name], $this->_properties[$name])
							: $dbref = $this->_properties[$name];
				
				$value = $dbref ? $this->_dereference($dbref) : null;
			}
		}
		else
		{
			$value = parent::__get($name);
		}
		
		return $value;
	}
	
	protected function &__get_id()
	{
		return $this->_id;
	}
	
	protected function __set_id($value)
	{
		$this->_id = $value;
	}
	
	protected function &__get__id()
	{
		return $this->_getMongoId('_id');
	}
	
	protected function __set__id($value)
	{
		$this->_setMongoId('_id', $value);
	}
	
	protected function &_getMongoId($name)
	{
		if($this->_properties[$name] instanceof MongoId)
		{
			return $this->_properties[$name]->{'$id'};
		}
		else
		{
			return $this->_properties[$name];
		}
	}
	
	protected function _setMongoId($name, $value)
	{
		if(is_string($value))
		{
			$this->_properties[$name] = new MongoId($value);
		}
		else
		{
			$this->_properties[$name] = $value;
		}
	}
	
	protected function &_getMongoDBRef($name)
	{
		if(@$this->_properties[$name]['$id'] instanceof MongoId)
		{
			return $this->_properties[$name]['$id']->{'$id'};
		}
		else
		{
			return $this->_properties[$name];
		}
	}
	
	protected function _setMongoDBRef($name, $value, $collection)
	{
		if(is_string($value))
		{
			$value = new MongoId($value);
		}
		if($value instanceof MongoId)
		{
			$this->_properties[$name] = MongoDBRef::create($collection, $value);
		}
		else
		{
			$this->_properties[$name] = $value;
		}
	}
	
	protected function &_getMongoDate($name)
	{
		if($this->_properties[$name] instanceof MongoDate)
		{
			$creationDate = date('c', $this->_properties[$name]->sec);
			return $creationDate;
		}
		else
		{
			return $this->_properties[$name];
		}
	}
	
	protected function _setMongoDate($name, $value)
	{
		if(is_string($value))
		{
			$value = new MongoDate(strtotime($value));
		}
		elseif (is_int($value) || is_float($value))
		{
			$value = new MongoDate($value);
		}
	
		$this->_properties[$name] = $value;
	}
	// }}} End Getters/Setters
}
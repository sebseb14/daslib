<?php

namespace Dasoft\Database;

use Kohana;

class Mongo extends \Mongo
{
	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';
	
	/**
	 * @var  array  Database instances
	 */
	public static $instances = array();
	
	/**
	 * @param string $name
	 * @param array $config
	 * @return \MongoDB
	 */
	public static function instance($name = NULL, array $config = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = self::$default;
		}
		
		if ( ! isset(self::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this database
				$config = Kohana::$config->load('mongo')->$name;
			}
		
			// Create the database connection instance
			$client = new static($config['server'], $config['options']);
			self::$instances[$name] = $client->selectDB($config['db']);
		}
		
		return self::$instances[$name];
	}
}
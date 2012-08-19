<?php 
namespace Dasoft;

define('DAKOLIB_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

function dakolibAutoload($class)
{
	$file = str_replace(array('_','\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), strtolower($class));
	$path = DAKOLIB_PATH . 'classes/' . $file. '.php';
	
	if(is_file($path))
	{
		include_once $path;
		return true;
	}
	
	return false;
}

spl_autoload_register('Dasoft\\dakolibAutoload');
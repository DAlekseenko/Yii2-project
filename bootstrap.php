<?php

/**
 * Girar-PHP bootstrap script.
 * 
 * Do not edit this file!
 * All changes will be lost after installation.
 */

define('GIRAR_BASE_DIR', dirname(__FILE__));
define('GIRAR_DATA_DIR', '/home/projects/data');
define('GIRAR_LOG_DIR', '/var/log/sender');

ini_set(
	'include_path',
	GIRAR_BASE_DIR.':/usr/share/php/pear'
);

function __girar_autoload($class)
{
	if (
		(@include str_replace('_', '/', $class).'.php') == 'OK' 
	) return true;
	else return false;
}

function __girar_autoload_psr4($class)
{
	if (
		(@include str_replace('\\', '/', $class).'.php') == 'OK' 
	) return true;
	else return false;
}

spl_autoload_register('__girar_autoload');
spl_autoload_register('__girar_autoload_psr4');

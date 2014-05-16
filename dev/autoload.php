<?php

function sw_autoloader($class) {
	$class = str_replace( array('\\', '_'), '/', $class );

	$path = __DIR__ . '/../src/' . $class . '.php';

	if ( file_exists($path) ) {
		return include_once $path;
	}

	$class = str_replace('Saltwater/', '', $class);

	$path = __DIR__ . '/../src/Module/' . $class . '.php';

	if ( file_exists($path) ) {
		return include_once $path;
	}

	return false;
}

spl_autoload_register('sw_autoloader');

<?php

function sw_autoloader($class) {
	$class = str_replace( array('\\', '_'), '/', $class );

	$path = __DIR__ . '/../src/' . $class . '.php';

	if ( file_exists($path) ) {
		include_once __DIR__ . '/../src/' . $class . '.php';
	}
}

spl_autoload_register('sw_autoloader');

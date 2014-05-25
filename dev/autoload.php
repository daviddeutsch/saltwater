<?php

function get_scenarios()
{
	static $scenarios;

	if ( empty($scenarios) ) {
		$scenarios = glob(__DIR__.'/suites/scenario/*', GLOB_ONLYDIR);
	}

	return $scenarios;
}

function sw_autoloader($class) {
	// Base classes
	$class = str_replace( array('\\', '_'), '/', $class );

	$path = __DIR__ . '/../src/' . $class . '.php';

	if ( file_exists($path) ) {
		return include_once $path;
	}

	// Core Modules
	$class = str_replace('Saltwater/', '', $class);

	$path = __DIR__ . '/../src/Module/' . $class . '.php';

	if ( file_exists($path) ) {
		return include_once $path;
	}

	$path = __DIR__ . '/suites/edge/Module/' . $class . '.php';

	if ( file_exists($path) ) {
		return include_once $path;
	}

	// Scenario Modules
	foreach ( get_scenarios() as $scenario ) {
		$path = $scenario . '/Module/' . $class . '.php';

		if ( file_exists($path) ) {
			return include_once $path;
		}
	}

	return false;
}

include_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register('sw_autoloader');

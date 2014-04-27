<?php

namespace Saltwater;

class Server
{
	/**
	 * @var Navigator
	 */
	public static $n;

	/**
	 * @var integer
	 */
	public static $start;

	/**
	 * Kick off the server with a set of modules.
	 *
	 * The first module is automatically the root module.
	 *
	 * @param string[] $modules Array of class names of modules to include
	 */
	public static function init( $modules=array() )
	{
		if ( empty(self::$start) ) self::$start = microtime(true);

		if ( empty(self::$n) ) self::$n = new Navigator();

		if ( !is_array($modules) ) $modules = array($modules);

		foreach ( $modules as $i => $module ) {
			self::$n->addModule($module, $i==0);
		}
	}

	/**
	 * Add a module to the Saltwater\Navigator module stack
	 *
	 * Proxy for Saltwater\Navigator::addModule()
	 *
	 * @param string $class
	 *
	 * @return bool|null
	 */
	public static function addModule( $class )
	{
		if ( empty(self::$n) ) self::init();

		return self::$n->addModule($class);
	}

	/**
	 * Return an Entity class name from the EntityProvider
	 *
	 * @param string $name
	 * @param null   $input
	 *
	 * @return Thing\Entity
	 */
	public static function entity( $name, $input=null )
	{
		return self::$n->entity($name, $input);
	}

	/**
	 * Halt the server and send a html header response
	 *
	 * @param int    $code
	 * @param string $message
	 */
	public static function halt( $code, $message )
	{
		header("HTTP/1.1 " . $code . " " . $message); exit;
	}
}

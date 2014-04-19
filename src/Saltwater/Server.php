<?php

namespace Saltwater;

class Server
{
	/**
	 * @var Navigator
	 */
	public static $n;

	/**
	 * Kick off the server with a set of modules.
	 *
	 * The first module is automatically the root module.
	 *
	 * @param string[] $modules Array of class names of modules to include
	 */
	public static function init( $modules=array() )
	{
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
	 * Set the root module in Saltwater\Navigator
	 *
	 * Proxy for Saltwater\Navigator::setRoot()
	 *
	 * @param string $name
	 */
	public static function setRoot( $name )
	{
		if ( empty(self::$n) ) self::init();

		self::$n->setRoot($name);
	}

	/**
	 * Set the master module in Saltwater\Navigator
	 *
	 * Proxy for Saltwater\Navigator::setMaster()
	 *
	 * @param $name
	 */
	public static function setMaster( $name )
	{
		if ( empty(self::$n) ) self::init();

		self::$n->setMaster($name);
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

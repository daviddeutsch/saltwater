<?php

namespace Saltwater;

class Server
{
	/**
	 * @var Navigator
	 */
	public static $n;

	/**
	 * @var float
	 */
	public static $start;

	/**
	 * Kick off the server with a set of modules.
	 *
	 * The first module is automatically the root module.
	 *
	 * @param string[] $modules array of class names of modules to include
	 * @param string   $cache   filepath to a navigator cache file
	 */
	public static function init( $modules=array(), $cache=null )
	{
		self::start();

		if ( empty($cache) ) {
			self::addModules($modules);
		} else {
			self::initCached($modules, $cache);
		}
	}

	private static function initCached( $modules, $cache )
	{
		if ( self::loadCache($cache) ) return;

		self::addModules($modules);

		self::$n->storeCache($cache);
	}

	/**
	 * Set timestamp and Navigator instance
	 */
	private static function start()
	{
		if ( !empty(self::$start) ) return;

		self::$start = microtime(true);

		self::$n = new Navigator();
	}

	/**
	 * @param string $cache
	 *
	 * @return bool
	 */
	private static function loadCache( $cache )
	{
		if ( !file_exists($cache) ) return false;

		return self::$n->loadCache($cache);
	}

	/**
	 * Add one or more modules to the Saltwater\Navigator module stack
	 *
	 * Proxy for Saltwater\Navigator::addModule()
	 *
	 * @param string[] $array
	 *
	 * @return bool|null
	 */
	public static function addModules( $array )
	{
		if ( empty(self::$start) ) self::init();

		if ( is_array($array) ) {
			foreach ( $array as $i => $module ) {
				self::$n->addModule($module, $i==0);
			}
		} else {
			self::$n->addModule($array);
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
		return self::$n->addModule($class);
	}

	/**
	 * Return an Entity class name from the EntityProvider
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	public static function entity( $name )
	{
		return self::$n->entity->get($name);
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

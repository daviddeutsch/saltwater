<?php

namespace Saltwater;

use Saltwater\Water\Navigator;

class Server
{
	/** @var Navigator */
	public static $n;

	/** @var float */
	public static $start;

	/** @var array */
	public static $gt = array();

	/**
	 * Kick off the server with a set of modules.
	 *
	 * The first module is automatically the root module.
	 *
	 * @param string[] $modules array of class names of modules to include
	 * @param string   $cache   filepath to a navigator cache file
	 *
	 * @return void
	 */
	public static function init( $modules=array(), $cache=null )
	{
		self::start();

		self::$gt['36'] = version_compare(phpversion(), '5.3.6', '>=');
		self::$gt['54'] = version_compare(phpversion(), '5.4.0', '>=');

		if ( empty($cache) ) {
			self::addModules($modules);
		} else {
			self::initCached($modules, $cache);
		}
	}

	/**
	 * Init Server from Cache
	 *
	 * @param $modules
	 * @param $cache
	 *
	 * @return void
	 */
	private static function initCached( $modules, $cache )
	{
		if ( self::loadCache($cache) ) return;

		self::addModules($modules);

		self::$n->storeCache($cache);
	}

	/**
	 * Set timestamp and Navigator instance
	 *
	 * @return void
	 */
	private static function start()
	{
		if ( !empty(self::$start) ) return;

		/** @var float $start */
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

		if ( !is_array($array) ) $array = array($array);

		foreach ( $array as $i => $module ) {
			self::$n->modules->append($module, $i==0);
		}
	}

	/**
	 * Add a module to the Saltwater\Navigator module stack
	 *
	 * Proxy for Saltwater\Navigator::addModule()
	 *
	 * @param string $class
	 * @param bool   $master
	 *
	 * @return bool|null
	 */
	public static function addModule( $class, $master=true )
	{
		return self::$n->modules->append($class, $master);
	}

	/**
	 * Halt the server and send a html header response
	 *
	 * @param int    $code
	 * @param string $message
	 *
	 * @return void
	 */
	public static function halt( $code, $message )
	{
		header("HTTP/1.1 " . $code . " " . $message);
	}

	/**
	 * Forget everything
	 *
	 * @return void
	 */
	public static function destroy()
	{
		self::$n = null;

		self::$start = null;
	}
}

<?php

namespace Saltwater;

use Saltwater\Utils as U;

class Server
{
	/**
	 * @var array|Thing\Module
	 */
	private static $modules = array();

	/**
	 * @var array|Thing\Provider
	 */
	private static $providers = array();

	/**
	 * @var array|Thing\Context
	 */
	private static $contexts = array();

	private static $root = 'root';

	private static $master = '';

	public static function init( $modules )
	{
		self::addModule('\Saltwater\Root\Root');

		if ( empty($modules) ) return;

		foreach ( $modules as $module ) {
			self::addModule($module);
		}
	}

	public static function addModule( $class )
	{
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		self::$modules[$name] = new $class();

		foreach ( self::$modules[$name]->providers() as $provider ) {
			$pname = U::CamelTodashed($provider);

			if ( !isset(self::$providers[$pname]) ) {
				self::$providers[$pname] = array();
			}

			self::$providers[$pname][] = $name;
		}

		foreach ( self::$modules[$name]->contexts() as $context ) {
			$pname = U::CamelTodashed($context);

			self::$contexts[$pname] = $name;
		}

		return true;
	}

	public static function setRoot( $name )
	{
		self::$root = $name;
	}

	public static function setMaster( $name )
	{
		self::$master = $name;
	}

	public static function provider( $name )
	{
		if ( !isset(self::$providers[$name]) ) {
			if ( in_array(self::$master, self::$providers[$name]) ) {
				return self::$modules[self::$master]->provider($name);
			} elseif ( in_array(self::$root, self::$providers[$name]) ) {
				return self::$modules[self::$root]->provider($name);
			} elseif ( in_array('root', self::$providers[$name]) ) {
				return self::$modules['root']->provider($name);
			} else {
				$key = array_shift( array_keys(self::$providers[$name]) );

				return self::$modules[$key]->provider($name);
			}
		} else {
			return false;
		}
	}

	public static function context( $name, $parent=null )
	{
		return self::provide('context', $name, $parent);
	}

	public static function service( $name, $context )
	{
		return self::provide('service', $name, $context);
	}

	public static function entity( $name, $bean=null )
	{
		return self::provide('entity', $name, $bean);
	}

	public static function provide( $type, $name, $input=null )
	{
		$provider = self::provider($type);

		if ( $provider === false ) return false;

		if ( is_null($input) ) {
			return $provider->get($name);
		} else {
			return $provider->get($name, $input);
		}
	}

	public static function __callStatic( $name, $arguments )
	{
		if ( empty($arguments) ) return self::provider($name);



	}

}

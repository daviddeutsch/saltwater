<?php

namespace Saltwater;

class Server
{
	/**
	 * @var Navigator
	 */
	public static $n;

	public static function init( $modules=array() )
	{
		if ( empty(self::$n) ) self::$n = new Navigator();

		foreach ( $modules as $i => $module ) {
			self::$n->addModule($module, $i==0);
		}
	}

	public static function addModule( $class )
	{
		if ( empty(self::$n) ) self::init();

		return self::$n->addModule($class);
	}

	public static function setRoot( $name )
	{
		if ( empty(self::$n) ) self::init();

		self::$n->setRoot($name);
	}

	public static function setMaster( $name )
	{
		if ( empty(self::$n) ) self::init();

		self::$n->setMaster($name);
	}

	public static function entity( $name, $input=null )
	{
		return self::$n->entity($name, $input);
	}

	public static function halt( $code, $message )
	{
		header("HTTP/1.1 " . $code . " " . $message); exit;
	}
}

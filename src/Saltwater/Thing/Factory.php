<?php

namespace Saltwater\Thing;

abstract class Factory
{
	protected static $module;

	protected function __construct() {}

	public static function setModule( $module )
	{
		self::$module = $module;
	}

	public static function get( $name, $input=null )
	{
		return new $name( $input );
	}
}

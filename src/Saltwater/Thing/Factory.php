<?php

namespace Saltwater\Thing;

use Saltwater\Interfaces\Factory as iFactory;

abstract class Factory implements iFactory
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

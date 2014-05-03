<?php

namespace Saltwater\Thing;

use Saltwater\Interfaces\Factory as FactoryInterface;

abstract class Factory implements FactoryInterface
{
	protected static $module;

	protected function __construct() {}

	public static function setModule( $module )
	{
		self::$module = $module;
	}

	public static function getFactory( $name, $input=null )
	{
		return new $name( $input );
	}
}

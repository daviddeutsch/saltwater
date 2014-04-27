<?php

namespace Saltwater\Thing;

use Saltwater\Interfaces\Provider as iProvider;

abstract class Provider implements iProvider
{
	protected static $module;

	protected function __construct() {}

	public static function setModule( $module )
	{
		self::$module = $module;
	}

	public static function get()
	{
		// return new Provider();

		return new \stdClass();
	}
}

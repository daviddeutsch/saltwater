<?php

namespace Saltwater\Thing;

abstract class Provider
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

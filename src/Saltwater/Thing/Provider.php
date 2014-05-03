<?php

namespace Saltwater\Thing;

use Saltwater\Interfaces\Provider as ProviderInterface;

abstract class Provider implements ProviderInterface
{
	protected static $module;

	protected function __construct() {}

	public static function setModule( $module )
	{
		self::$module = $module;
	}

	public static function getProvider()
	{
		// return new Provider();

		return new \stdClass();
	}
}

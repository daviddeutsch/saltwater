<?php

namespace Saltwater\Thing;

use Saltwater\Interfaces\Provider as ProviderInterface;

abstract class Provider implements ProviderInterface
{
	protected static $module;

	protected static $caller;

	protected function __construct() {}

	public static function setModule( $module )
	{
		self::$module = $module;
	}

	public static function setCaller( $caller )
	{
		if ( empty($caller) ) $caller = 'root';

		self::$caller = $caller;
	}

	public static function getProvider()
	{
		// return new Provider();

		return new \stdClass;
	}
}

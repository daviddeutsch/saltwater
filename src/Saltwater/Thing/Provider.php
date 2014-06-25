<?php

namespace Saltwater\Thing;

abstract class Provider
{
	/** @var string */
	protected static $module;

	/** @var string */
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

	/*
	public static function getProvider()
	{
		return new Provider();
	}
	*/
}

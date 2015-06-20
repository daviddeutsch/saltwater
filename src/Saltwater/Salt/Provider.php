<?php

namespace Saltwater\Salt;

/**
 * Providers encapsulate business logic
 *
 * @package Saltwater\Salt
 */
abstract class Provider
{
	/** @var string */
	protected static $module;

	/** @var string */
	protected static $caller;

	/**
	 * @param string $module
	 */
	public static function setModule( $module )
	{
		self::$module = $module;
	}

	/**
	 * @param string $caller
	 */
	public static function setCaller( $caller )
	{
		self::$caller = empty($caller) ? 'root' : $caller;
	}
}

<?php

namespace Saltwater\Salt;

abstract class Provider
{
	/** @var string */
	protected static $module;

	/** @var string */
	protected static $caller;

	/**
	 * Protected constructor, instantiate through ::getProvider()
	 */
	protected function __construct() {}

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

	/**
	 * @return Provider
	 */
	public static function getProvider()
	{
		return new \stdClass();
	}
}

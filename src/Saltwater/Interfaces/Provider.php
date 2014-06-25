<?php

namespace Saltwater\Interfaces;

/**
 * Provider Interface
 *
 * A Provider returns Things
 */
interface Provider
{
	/**
	 * @return void
	 */
	public static function setModule( $module );

	/**
	 * @return object
	 */
	public static function getProvider();
}

<?php

namespace Saltwater\Interfaces;

/**
 * Interface Provider
 *
 * A Provider returns Things
 */
interface Provider
{
	/**
	 * @return void
	 */
	public static function setModule( $module );

	public static function getProvider();
}

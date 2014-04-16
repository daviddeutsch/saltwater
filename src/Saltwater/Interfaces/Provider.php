<?php

namespace Saltwater\Interfaces;

/**
 * Interface Provider
 *
 * A Provider returns Things
 */
interface Provider
{
	public static function setModule( $module );

	public static function get();
}

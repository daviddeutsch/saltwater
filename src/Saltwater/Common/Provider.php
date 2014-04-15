<?php

namespace Saltwater\Common;

/**
 * Interface Provider
 *
 * A Provider returns Things
 */
interface Provider
{
	public static function get( $input=null );
}

<?php

namespace Saltwater\Common;

/**
 * Interface Factory
 *
 * A Factory is a Provider that returns Things that are most likely not itself
 */
interface Factory
{
	public static function get( $name, $input=null );
}

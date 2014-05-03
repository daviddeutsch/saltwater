<?php

namespace Saltwater\Interfaces;

/**
 * Interface Factory
 *
 * A Factory is a Provider that returns Things that are most likely not itself
 */
interface Factory
{
	public static function setModule( $module );

	public static function getFactory( $name, $input=null );
}

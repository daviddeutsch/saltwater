<?php

namespace Saltwater\Thing;

use Saltwater\Utils as U;

class Module
{
	protected static $contexts = array();

	public static function findContext()
	{
		foreach ( self::$contexts as $context ) {
			$class = 'Saltwater\\' . U::dashedToCamelCase($context);

			if ( !class_exists($class) ) return $class;
		}

		return false;
	}
}

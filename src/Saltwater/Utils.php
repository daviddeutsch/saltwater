<?php

namespace Saltwater;

class Utils
{
	public static function snakeToCamelCase( $string )
	{
		return self::CamelCaseSpaced( str_replace('_', ' ', $string) );
	}

	public static function dashedToCamelCase( $string )
	{
		return self::CamelCaseSpaced( str_replace('-', ' ', $string) );
	}

	public static function CamelCaseSpaced( $string )
	{
		return str_replace(' ', '', ucwords($string) );
	}

	public static function CamelTodashed( $string )
	{
		return strtolower(
			preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string)
		);
	}

	public static function namespacedClassToDashed( $string )
	{
		return self::CamelTodashed( array_pop( explode('\\', $string) ) );
	}
}

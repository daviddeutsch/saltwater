<?php

namespace Saltwater;

class Utils
{
	/**
	 * Convert snake_case to CamelCase
	 *
	 * @param string $string
	 *
	 * @return mixed
	 */
	public static function snakeToCamelCase( $string )
	{
		return self::CamelCaseSpaced( str_replace('_', ' ', $string) );
	}

	/**
	 * Convert dashed-case to CamelCase
	 *
	 * @param string $string
	 *
	 * @return mixed
	 */
	public static function dashedToCamelCase( $string )
	{
		return self::CamelCaseSpaced( str_replace('-', ' ', $string) );
	}

	/**
	 * Convert a camel cased Object into a CamelCasedObject
	 *
	 * @param string $string
	 *
	 * @return mixed
	 */
	public static function CamelCaseSpaced( $string )
	{
		return str_replace(' ', '', ucwords($string) );
	}

	/**
	 * Convert a CamelCasedObject into a dashed-case-object
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function CamelTodashed( $string )
	{
		return strtolower(
			preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string)
		);
	}

	/**
	 * Convert a /Namespaced/Class to a dashed-class
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function namespacedClassToDashed( $string )
	{
		$array = explode('\\', $string);

		return self::CamelTodashed( array_pop( $array ) );
	}
}

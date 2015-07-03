<?php

namespace Saltwater;

use Saltwater\Server as S;

class Utils
{
	/**
	 * Convert snake_case to CamelCase
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function snakeToCamelCase( $string )
	{
		return self::camelCaseSpaced( str_replace('_', ' ', $string) );
	}

	/**
	 * Convert dashed-case to CamelCase
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function dashedToCamelCase( $string )
	{
		return self::camelCaseSpaced( str_replace('-', ' ', $string) );
	}

	/**
	 * Convert a camel cased Object into a CamelCasedObject
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function camelCaseSpaced( $string )
	{
		return str_replace( ' ', '', ucwords($string) );
	}

	/**
	 * Convert a CamelCasedObject into a dashed-case-object
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function camelTodashed( $string )
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

		return self::camelTodashed( array_pop($array) );
	}

	/**
	 * Convert a /Namespaced/Class to a dashed-class
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function namespaceFromClass( $string )
	{
		$array = explode('\\', $string);

		array_pop($array);

		return implode('\\', $array);
	}

	/**
	 * @return string
	 */
	public static function className()
	{
		$args = array();
		foreach ( func_get_args() as $arg ) {
			if ( strpos($arg, '_') ) {
				$args[] = self::snakeToCamelCase($arg);
			} else {
				$args[] = self::dashedToCamelCase($arg);
			}
		}

		return implode('\\', $args);
	}

	/**
	 * @param string|object $input
	 *
	 * @return array
	 */
	public static function explodeClass( $input )
	{
		if ( is_object($input) ) $input = get_class($input);

		return explode('\\', $input );
	}

	/**
	 * @param array $class
	 *
	 * @return array Class Name, Salt and Namespace
	 */
	public static function extractFromClass( $class )
	{
		$class_name = array_pop($class);

		$salt = strtolower( array_pop($class) . '.' . $class_name );

		$namespace = implode('\\', $class);

		return array($class_name, $salt, $namespace);
	}

	/**
	 * Read a JSON file and return its content
	 *
	 * @param      $path
	 * @param bool $associative
	 *
	 * @return mixed
	 */
	public static function getJSON( $path, $associative=false )
	{
		return json_decode( file_get_contents($path), $associative );
	}

	/**
	 * Store any data as JSON to a file
	 *
	 * @param $path
	 * @param $content
	 *
	 * @return int
	 */
	public static function storeJSON( $path, $content )
	{
		if ( S::$env['gt54'] ) {
			return file_put_contents(
				$path,
				json_encode(
					$content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
				)
			);
		}

		return file_put_contents( $path, json_encode($content) );
	}
}

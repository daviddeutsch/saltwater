<?php

namespace Saltwater;

class Utils
{
	public static function snakeToCamelCase( $string )
	{
		return str_replace(' ', '', ucwords( str_replace('_', ' ', $string) ) );
	}

	public static function dashedToCamelCase( $string )
	{
		return str_replace(' ', '', ucwords( str_replace('-', ' ', $string) ) );
	}
}

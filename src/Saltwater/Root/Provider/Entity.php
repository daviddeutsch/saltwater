<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Factory;

class Entity extends Factory
{
	public static function get( $name, $input=null )
	{
		$model = self::formatModel($name);

		if ( !empty($model) ) return $model;

		return $name;
	}

	private static function formatModel( $name )
	{
		$module = S::$n->getModule(self::$module);

		$name = U::snakeToCamelCase($name);

		$class = $module->namespace . '\Entity\\' . $name;

		if ( class_exists($class) ) return $class;

		$name = U::CamelTodashed($name);

		$bit = S::$n->bitThing('entity.' . $name);

		if ( $module->hasThing($bit) ) {
			return '\Saltwater\Thing\Entity';
		}

		return null;
	}
}

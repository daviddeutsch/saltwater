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

		if ( !empty($model) ) {
			return $model;
		}

		return $name;
	}

	private static function formatModel( $name )
	{
		$module = S::$n->getModule(self::$module);

		$name = U::snakeToCamelCase($name);

		$class = $module->namespace . '\Entity\\' . $name;

		if ( class_exists($class) ) {
			return $class;
		} else {
			$name = U::CamelTodashed($name);

			if ( in_array($name, $module->entities()) ) {
				return '\Saltwater\Thing\Entity';
			}
		}

		return null;
	}
}

<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Thing\Factory;

class Entity extends Factory
{
	public static function get( $name, $input=null )
	{
		$module = S::$n->getModule(self::$module);

		$model = $module->formatModel($name);

		if ( !empty($model) ) {
			return $model;
		}

		return $name;
	}
}

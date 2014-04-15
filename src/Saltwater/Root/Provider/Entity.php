<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Common\Factory;

class Entity implements Factory
{
	public static function get( $name, $input=null )
	{
		foreach ( S::$context as $context ) {
			$model = $context->formatModel($name);

			if ( !empty($model) ) {
				return $model;
			}
		}

		return $name;
	}
}

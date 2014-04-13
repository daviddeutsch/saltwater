<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;

class Entity
{
	public function get( $name )
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

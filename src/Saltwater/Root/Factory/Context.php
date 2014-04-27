<?php

namespace Saltwater\Root\Factory;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Factory;

class Context extends Factory
{
	public static function get( $name, $context=null )
	{
		$module = S::$n->getContextModule($name);

		if ( empty($module) ) return false;

		$class = $module->namespace
			. '\Context\\'
			. U::dashedToCamelCase($name);

		if ( !class_exists($class) ) return false;

		return new $class($context);
	}
}

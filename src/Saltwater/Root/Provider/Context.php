<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Factory;

class Context extends Factory
{
	public static function get( $name, $context=null )
	{
		$module = self::$module;

		foreach ( S::$n->getContexts() as $c => $m ) {
			if ( $c == $name ) {
				$module = $m;
			}
		}

		$module = S::$n->getModule($module);

		$class = $module->namespace
			. '\Context\\'
			. U::dashedToCamelCase($name);

		if ( !class_exists($class) ) return false;

		return new $class($context);
	}
}

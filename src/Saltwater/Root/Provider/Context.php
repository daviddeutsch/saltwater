<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Factory;

class Context extends Factory
{
	public static function get( $name, $context=null )
	{
		$module = S::$n->getModule(self::$module);

		$class = $module->namespace
			. '\Context\\'
			. U::dashedToCamelCase($name);

		if ( !class_exists($class) ) return false;

		S::setMaster(self::$module);

		return new $class($context);
	}
}

<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Provider;

class Context extends Provider
{
	public static function getProvider() { return new Context(); }

	/**
	 * @param string                   $name
	 * @param \Saltwater\Thing\Context $context Parent Context
	 *
	 * @return \Saltwater\Thing\Context
	 */
	public function get( $name, $context=null )
	{
		$module = S::$n->getContextModule($name);

		if ( empty($module) ) return false;

		$class = $module->namespace
			. '\Context\\'
			. U::dashedToCamelCase($name);

		if ( !class_exists($class) ) return false;

		return new $class($context, $module);
	}
}

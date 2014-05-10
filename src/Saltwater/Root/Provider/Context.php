<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Provider;

class Context extends Provider
{
	public static function getProvider() { return new Context(); }

	/**
	 * @param string                        $name
	 * @param \Saltwater\Thing\Context|null $context Parent Context
	 *
	 * @return \Saltwater\Thing\Context|null
	 */
	public function get( $name, $context=null )
	{
		$module = S::$n->getContextModule($name);

		if ( empty($module) ) return null;

		$class = $module->namespace
			. '\Context\\'
			. U::dashedToCamelCase($name);

		if ( !class_exists($class) ) return null;

		return $this->newContext($class, $context, $module);
	}

	/**
	 * @param string                        $class
	 * @param \Saltwater\Thing\Context|null $context
	 * @param \Saltwater\Thing\Module       $module
	 *
	 * @return \Saltwater\Thing\Context
	 */
	private function newContext( $class, $context, $module )
	{
		return new $class($context, $module);
	}
}

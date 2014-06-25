<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Provider;
use Saltwater\Salt\Context as SwContext;
use Saltwater\Salt\Module;

class Context extends Provider
{
	public static function getProvider()
	{
		return new Context;
	}

	/**
	 * @param string         $name
	 * @param SwContext|null $context Parent Context
	 *
	 * @return SwContext|null
	 */
	public function get( $name, $context=null )
	{
		$module = S::$n->getContextModule($name);

		if ( empty($module) ) return null;

		$class = U::className($module::getNamespace(), 'context', $name);

		if ( !class_exists($class) ) return null;

		return $this->newContext($class, $context, $module);
	}

	/**
	 * @param string         $class
	 * @param SwContext|null $context
	 * @param Module         $module
	 *
	 * @return SwContext
	 */
	private function newContext( $class, $context, $module )
	{
		return new $class($context, $module);
	}
}

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
	 * @param SwContext|null $parent
	 *
	 * @return SwContext|null
	 */
	public function get( $name, $parent=null )
	{
		$module = S::$n->getContextModule($name);

		if ( empty($module) ) return null;

		$class = U::className($module::getNamespace(), 'context', $name);

		if ( !class_exists($class) ) return null;

		return $this->newContext($class, $parent, $module);
	}

	/**
	 * @param string         $class
	 * @param SwContext|null $parent
	 * @param Module         $module
	 *
	 * @return SwContext
	 */
	private function newContext( $class, $parent, $module )
	{
		return new $class($parent, $module);
	}
}

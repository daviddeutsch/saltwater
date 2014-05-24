<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Thing\Provider;

class Entity extends Provider
{
	public static function getProvider() { return new Entity; }

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function get( $name )
	{
		$model = $this->formatModel($name);

		if ( !empty($model) ) return $model;

		return $name;
	}

	/**
	 * @param string $name
	 *
	 * @return string|null
	 */
	private function formatModel( $name )
	{
		$bit = S::$n->bitThing('entity.' . $name);

		// TODO: This is a bit wasteful since self::$caller is very likely to work
		$injected = S::$n->moduleByThing('entity.' . $name);

		foreach ( array(self::$caller, $injected, self::$module) as $m ) {
			$module = S::$n->getModule($m);

			if ( !is_object($module) ) continue;

			if ( !$module->has($bit) ) continue;

			$class = $this->fromModule($name, $module);

			if ( class_exists($class) ) {
				return $class;
			} else {
				return 'Saltwater\RedBean\Thing\Entity';
			}
		}

		return null;
	}

	/**
	 * @param string                  $name
	 * @param \Saltwater\Thing\Module $module
	 *
	 * @return string
	 */
	private function fromModule( $name, $module )
	{
		return U::className($module::getNamespace(), 'entity', $name);
	}
}

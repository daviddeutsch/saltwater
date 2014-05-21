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

		$injected = S::$n->moduleByThing('entity.' . $name);

		foreach ( array($injected, self::$caller, self::$module) as $name ) {
			$module = S::$n->getModule($name);

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
		return U::className($module::$namespace, 'entity', $name);
	}
}

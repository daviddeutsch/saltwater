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

		if ( !$bit ) return null;

		if ( $class = $this->entityFromModule(self::$caller, $bit) ) {
			return $class;
		}

		$injected = S::$n->moduleByThing('entity.' . $name);

		if ( $class = $this->entityFromModule($injected, $bit) ) {
			return $class;
		}

		if ( $class = $this->entityFromModule(self::$module, $bit) ) {
			return $class;
		}

		return null;
	}

	private function entityFromModule( $name, $bit )
	{
		$module = S::$n->getModule($name);

		if ( !is_object($module) ) return false;

		if ( !$module->has($bit) ) return false;

		$class = $this->fromModule($name, $module);

		return class_exists($class) ? $class : 'Saltwater\RedBean\Thing\Entity';
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

<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Provider;
use Saltwater\Salt\Module;

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
		$bit = S::$n->bitSalt('entity.' . $name);

		if ( !$bit ) return null;

		if ( $class = $this->entityFromModule(self::$caller, $name, $bit) ) {
			return $class;
		}

		$injected = S::$n->moduleBySalt('entity.' . $name);

		if ( $class = $this->entityFromModule($injected, $name, $bit) ) {
			return $class;
		}

		if ( $class = $this->entityFromModule(self::$module, $name, $bit) ) {
			return $class;
		}

		return null;
	}

	private function entityFromModule( $module, $name, $bit )
	{
		$module = S::$n->getModule($module);

		if ( !is_object($module) || !$module->has($bit) ) return false;

		$class = $this->fromModule($name, $module);

		return class_exists($class) ? $class : 'Saltwater\RedBean\Salt\Entity';
	}

	/**
	 * @param string $name
	 * @param Module $module
	 *
	 * @return string
	 */
	private function fromModule( $name, $module )
	{
		return U::className($module::getNamespace(), 'entity', $name);
	}
}

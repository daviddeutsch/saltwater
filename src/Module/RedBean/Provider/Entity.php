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
	 * Format a full class name from an entity name
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	private function formatModel( $name )
	{
		$bit = S::$n->registry->bit('entity.' . $name);

		if ( !$bit ) return null;

		return $this->findModel($name, $bit);
	}

	private function findModel( $name, $bit )
	{
		if ( $class = $this->entityFromModule(self::$caller, $name, $bit) ) {
			return $class;
		}

		if ( $class = $this->entityFromModule(
			S::$n->modules->finder->moduleBySalt('entity.' . $name),
			$name,
			$bit
		) ) return $class;

		if ( $class = $this->entityFromModule(self::$module, $name, $bit) ) {
			return $class;
		}

		return null;
	}

	/**
	 * Check whether a module has an entity declared, if so, either load the
	 * entity class, or fall back to the basic salt
	 *
	 * @param Module|string $module
	 * @param string        $name
	 * @param int           $bit
	 *
	 * @return bool|string
	 */
	private function entityFromModule( $module, $name, $bit )
	{
		$module = S::$n->modules->get($module);

		if ( !is_object($module) || !$module->has($bit) ) return false;

		$class = $this->getEntityClass($name, $module);

		return class_exists($class) ? $class : 'Saltwater\RedBean\Salt\Entity';
	}

	/**
	 * Get Entity class name from a name plus a module
	 * @param string $name
	 * @param Module $module
	 *
	 * @return string
	 */
	private function getEntityClass( $name, $module )
	{
		return U::className($module::getNamespace(), 'entity', $name);
	}
}

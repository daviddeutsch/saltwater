<?php

namespace Saltwater\RedBean\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Provider;
use Saltwater\Salt\Module;

class Entity extends Provider
{
	/**
	 * @param string $name
	 *
	 * @return string
	 */
	public function get( $name )
	{
		$entity = $this->format($name);

		if ( !empty($entity) ) return $entity;

		return $name;
	}

	/**
	 * Format a full class name from an entity name
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	private function format( $name )
	{
		$bit = S::$n->registry->bit('entity.' . $name);

		if ( !$bit ) return null;

		return $this->find($name, $bit);
	}

	/**
	 * Search for an entity implementation in the module stack
	 *
	 * @param string $name
	 * @param int    $bit
	 *
	 * @return bool|null|string
	 */
	private function find( $name, $bit )
	{
		// Try to find the entity within the module that is calling us
		if ( $class = $this->fromModule(self::$caller, $name, $bit) ) {
			return $class;
		}

		// Otherwise try to find it by going up the module stack
		$modules = S::$n->modules->finder->modulesBySalt('entity.' . $name);

		foreach ( $modules as $module ) {
			if ( $class = $this->fromModule($module, $name, $bit) ) {
				return $class;
			}
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
	private function fromModule( $module, $name, $bit )
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

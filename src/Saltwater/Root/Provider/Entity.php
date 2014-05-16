<?php

namespace Saltwater\Root\Provider;

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
		$module = $this->getCallerModule();

		$bit = S::$n->bitThing('entity.' . $name);

		if ( !$module->has($bit) ) {
			$module = $this->getOwnModule();
		}

		$class = $this->fromModule($name, $module);

		if ( class_exists($class) ) return $class;

		if ( $module->has($bit) ) {
			return '\Saltwater\Thing\Entity';
		}

		return null;
	}

	/**
	 * @return \Saltwater\Thing\Module
	 */
	private function getCallerModule()
	{
		return S::$n->getModule(self::$caller);
	}

	/**
	 * @return \Saltwater\Thing\Module
	 */
	private function getOwnModule()
	{
		return S::$n->getModule(self::$module);
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

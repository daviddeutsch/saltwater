<?php

namespace Saltwater\Salt;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Provider;

/**
 * Module
 *
 * @package Saltwater\Salt
 *
 * An object that can register and return salts
 */
class Module
{
	/** @var string Can be used to override the automatically generated value */
	public static $name;

	/** @var string Can be used to override the automatically generated value */
	public static $namespace;

	/**
	 * @var array Requirements that need to be in place for this module
	 */
	protected $require = array();

	/**
	 * @var array Salts that this module provides
	 */
	protected $provide = array();

	/**
	 * @var int bitmask of Salts passed to the registry
	 */
	public $registry = 0;

	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function register( $name )
	{
		$name = 'module.' . $name;

		if ( S::$n->registry->exists($name) || $this->registry ) return;

		$this->ensureRequires();

		$this->registry |= S::$n->registry->append($name);

		$this->registerProvides();
	}

	/**
	 * Make sure all required modules are loaded
	 *
	 * @return void
	 */
	private function ensureRequires()
	{
		if ( empty($this->require['module']) ) return;

		foreach ( $this->require['module'] as $module ) {
			S::$n->modules->append($module, true);
		}
	}

	/**
	 * Register all salts the module provides
	 *
	 * @return void
	 */
	private function registerProvides()
	{
		if ( empty($this->provide) ) return;

		foreach ( $this->provide as $type => $content ) {
			foreach ( $content as $salt ) {
				$this->registerProvide($type, $salt);
			}
		}
	}

	/**
	 * Register a salt that this module provides
	 *
	 * @param $type
	 * @param $salt
	 *
	 * @return void
	 */
	private function registerProvide( $type, $salt )
	{
		$this->registry |= S::$n->registry->append(
			$type . '.' . U::camelTodashed($salt)
		);
	}

	/**
	 * Check whether the module contains a salt
	 *
	 * @param $bit
	 *
	 * @return bool
	 */
	public function has( $bit )
	{
		return ($this->registry & $bit) == $bit;
	}

	/**
	 * Create a new provider instance
	 *
	 * @param string $type
	 * @param string $caller
	 *
	 * @return Provider
	 */
	public function provider( $caller, $type )
	{
		$class = $this->makeProvider($type);

		if ( $class === false ) return false;

		$class::setModule($this::getName());

		$class::setCaller($caller);

		return $class::getProvider();
	}

	/**
	 * @param string $type
	 *
	 * @return false|Provider
	 */
	private function makeProvider( $type )
	{
		$class = U::className($this::getNamespace(), 'provider', $type);

		return class_exists($class) ? $class : false;
	}

	/**
	 * Check whether this module provides a context
	 *
	 * @return bool
	 */
	public function lacksContext()
	{
		return empty($this->provide['context']);
	}

	/**
	 * Return the master context for this module
	 *
	 * @return null|string
	 */
	public function masterContext()
	{
		if ( $this->lacksContext() ) return null;

		return U::camelTodashed( $this->provide['context'][0] );
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		$class = get_called_class();

		if ( !empty($class::$name) ) return $class::$name;

		return U::namespacedClassToDashed($class);
	}

	/**
	 * @return string
	 */
	public static function getNamespace()
	{
		$class = get_called_class();

		if ( !empty($class::$namespace) ) return $class::$namespace;

		return U::namespaceFromClass($class);
	}
}

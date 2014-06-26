<?php

namespace Saltwater\Salt;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Salt\Provider;

/**
 * An object that can register and return providers, contexts and services
 */
class Module
{
	public static $name;

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
	 */
	public function register( $name )
	{
		$name = 'module.' . $name;

		if ( S::$n->isSalt($name) || $this->registry ) return null;

		$this->ensureRequires();

		$this->registry |= S::$n->addSalt($name);

		$this->registerProvides();
	}

	private function ensureRequires()
	{
		if ( empty($this->require['module']) ) return;

		foreach ( $this->require['module'] as $module ) {
			S::$n->addModule($module, true);
		}
	}

	private function registerProvides()
	{
		if ( empty($this->provide) ) return;

		foreach ( $this->provide as $type => $content ) {
			foreach ( $content as $Salt ) {
				$this->registerProvide($type, $Salt);
			}
		}
	}

	private function registerProvide( $type, $Salt )
	{
		$this->registry |= S::$n->addSalt(
			$type . '.' . U::camelTodashed($Salt)
		);
	}

	public function has( $bit )
	{
		return ($this->registry & $bit) == $bit;
	}

	/**
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

	public function lacksContext()
	{
		return empty($this->provide['context']);
	}

	public function masterContext()
	{
		if ( $this->lacksContext() ) return null;

		return U::camelTodashed( $this->provide['context'][0] );
	}

	public static function getName()
	{
		$class = get_called_class();

		if ( !empty($class::$name) ) return $class::$name;

		return U::namespacedClassToDashed($class);
	}

	public static function getNamespace()
	{
		$class = get_called_class();

		if ( !empty($class::$namespace) ) return $class::$namespace;

		return U::namespaceFromClass($class);
	}
}

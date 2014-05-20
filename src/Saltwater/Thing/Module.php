<?php

namespace Saltwater\Thing;

use Saltwater\Server as S;
use Saltwater\Utils as U;

/**
 * An object that can register and return providers, contexts and services
 */
class Module
{
	public static $name;

	public static $namespace;

	/**
	 * @var array Associative Array of requirements that need to be in place
	 *            for this module
	 */
	protected $require = array();

	/**
	 * @var array Associative Array of things that this module provides
	 */
	protected $provide = array();

	/**
	 * @var int bitmask of things passed to the registry
	 */
	public $registry = 0;

	/**
	 * @param string $name
	 */
	public function register( $name )
	{
		if ( S::$n->isThing('module.' . $name) || $this->registry ) return null;

		$this->ensureRequires();

		$this->registry |= S::$n->addThing('module.' . $name);

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
			foreach ( $content as $thing ) {
				$this->registerProvide($type, $thing);
			}
		}
	}

	private function registerProvide( $type, $thing )
	{
		$this->registry |= S::$n->addThing(
			$type . '.' . U::CamelTodashed($thing)
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
	 * @return \Saltwater\Thing\Provider
	 */
	public function provider( $caller, $type )
	{
		$class = $this->makeProvider($type);

		if ( !$class ) return false;

		$class::setModule($this::$name);

		$class::setCaller($caller);

		return $class::getProvider();
	}

	/**
	 * @param string $type
	 *
	 * @return false|\Saltwater\Thing\Provider
	 */
	private function makeProvider( $type )
	{
		$class = U::className($this::$namespace, 'provider', $type);

		return class_exists($class) ? $class : false;
	}

	public function noContext()
	{
		return empty($this->provide['context']);
	}

	public function masterContext()
	{
		if ( $this->noContext() ) return null;

		return U::CamelTodashed( $this->provide['context'][0] );
	}
}

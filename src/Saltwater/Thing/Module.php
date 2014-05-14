<?php

namespace Saltwater\Thing;

use Saltwater\Server as S;
use Saltwater\Utils as U;

/**
 * An object that can register and return providers, contexts and services
 */
class Module
{
	public $namespace;

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
	 * @var int bitmask of thing registry
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

		$this->registerThings();
	}

	private function ensureRequires()
	{
		if ( empty($this->require['module']) ) return;

		foreach ( $this->require['module'] as $module ) {
			S::$n->addModule($module);
		}
	}

	private function registerThings()
	{
		if ( empty($this->provide) ) return;

		foreach ( $this->provide as $type => $content ) {
			foreach ( $content as $thing ) {
				$this->registry |= S::$n->addThing(
					$type . '.' . U::CamelTodashed($thing)
				);
			}
		}
	}

	public function hasThing( $bit )
	{
		return ($this->registry & $bit) == $bit;
	}

	/**
	 * @param string $module
	 * @param string $type
	 * @param string $caller
	 *
	 * @return \Saltwater\Thing\Provider
	 */
	public function provider( $module, $caller, $type )
	{
		$class = $this->className('provider', $type);

		if ( !class_exists($class) ) return false;

		$class::setModule($module);
		$class::setCaller($caller);

		return $class::getProvider();
	}

	public function masterContext()
	{
		if ( empty($this->contexts) ) return false;

		return U::CamelTodashed( $this->contexts[0] );
	}

	/**
	 * @param string $type
	 * @param $name
	 *
	 * @return string
	 */
	protected function className( $type, $name )
	{
		return $this->namespace
			. '\\' . ucfirst($type)
			. '\\' . U::dashedToCamelCase($name);
	}
}

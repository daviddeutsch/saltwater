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

	protected $dependencies = array();

	protected $providers = array();

	protected $contexts = array();

	protected $services = array();

	protected $entities = array();

	/**
	 * @var int bitmask of thing registry
	 */
	public $things = 0;

	/**
	 * @param string $name
	 */
	public function register( $name )
	{
		if ( S::$n->isThing('module.' . $name) || $this->things ) return null;

		$this->registerDependencies();

		$this->things |= S::$n->addThing('module.' . $name);

		$this->registerThings();
	}

	private function registerDependencies()
	{
		if ( empty($this->dependencies) ) return;

		foreach ( $this->dependencies as $dependency ) {
			S::$n->addModule($dependency);
		}
	}

	private function registerThings()
	{
		foreach ( $this->thingTypes() as $p => $s ) {
			if ( empty($this->$p) ) continue;

			foreach ( $this->$p as $thing ) {
				$this->things |= S::$n->addThing(
					$s . '.' . U::CamelTodashed($thing)
				);
			}
		}
	}

	public function hasThing( $bit )
	{
		return ($this->things & $bit) == $bit;
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

	public function provideList( $type )
	{
		if ( empty($this->$type) ) return array();

		return $this->$type;
	}

	public function masterContext()
	{
		if ( empty($this->contexts) ) return false;

		return U::CamelTodashed( $this->contexts[0] );
	}

	public function thingTypes()
	{
		return array(
			'providers' => 'provider',
			'contexts' => 'context',
			'services' => 'service',
			'entities' => 'entity'
		);
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

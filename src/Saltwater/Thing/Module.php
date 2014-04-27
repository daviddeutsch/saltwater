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
	protected $things;

	public function register( $name )
	{
		if ( S::$n->isThing('module.' . $name) ) return null;

		$this->things = 0;

		if ( !empty($this->dependencies) ) {
			foreach ( $this->dependencies as $dependency ) {
				S::$n->addModule($dependency);
			}
		}

		$this->things |= S::$n->addThing('module.' . $name);

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
	 * @param $type
	 * @param $name
	 * @param $args
	 *
	 * @return \Saltwater\Thing\Provider
	 */
	public function provide( $module, $name, $args=null )
	{
		$class = $this->className('provider', $name);

		if ( !class_exists($class) ) return false;

		$class::setModule($module);

		if ( is_null($args) ) {
			return $class::get();
		} elseif ( is_array($args) && !empty($args) ) {
			return $class::get($args[0], $args[1]);
		} else {
			return $class::get($args);
		}
	}

	public function provideList( $type )
	{
		if ( empty($this->$type) ) return array();

		return $this->$type;
	}

	public function masterContext()
	{
		if ( empty($this->contexts) ) return 'root';

		return array(
			'root',
			U::CamelTodashed( array_shift($this->contexts) )
		);
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
	 * @param $type
	 * @param $name
	 *
	 * @return \Saltwater\Thing\Provider
	 */
	protected function className( $type, $name )
	{
		return $this->namespace
			. '\\' . ucfirst($type)
			. '\\' . U::dashedToCamelCase($name);
	}
}

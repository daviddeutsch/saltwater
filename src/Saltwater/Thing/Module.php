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
		$things = 0;

		$things |= S::$n->addThing('model.' . $name);

		if ( !empty($this->dependencies) ) {
			foreach ( $this->dependencies as $dependency ) {
				S::$n->addModule($dependency);
			}
		}

		foreach (
			array(
				'providers' => 'provider',
				'contexts' => 'context',
				'services' => 'service',
				'entities' => 'entity'
			) as $p => $s
		) {
			if ( empty($this->$p) ) continue;

			foreach ( $this->$p as $thing ) {
				$things |= S::$n->addThing(
					$s . '.' . U::CamelTodashed($thing)
				);
			}
		}

		$this->things = $things;
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
		} elseif ( is_array($args) ) {
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
		if ( empty($this->contexts) ) {
			return 'root';
		} else {
			return array(
				'root',
				U::CamelTodashed( array_shift($this->contexts) )
			);
		}
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

<?php

namespace Saltwater\Thing;

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
	 * @param $type
	 * @param $name
	 * @param $args
	 *
	 * @return \Saltwater\Thing\Provider
	 */
	public function provide( $module, $name, $args=null )
	{
		// TODO: Figure out why this, why that.
		if ( is_array($name) ) {
			$copy = $name;

			$name = array_shift($copy);

			$args = $copy;
		}

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

	public function dependencies() { return $this->dependencies; }

	public function providers() { return $this->provideList('providers'); }

	public function contexts() { return $this->provideList('contexts'); }

	public function services() { return $this->provideList('services'); }

	public function entities() { return $this->provideList('entities'); }

	public function provideList( $type )
	{
		if ( empty($this->$type) ) return array();

		return $this->$type;
	}

	public function masterContext()
	{
		$contexts = $this->contexts();

		return array('root', U::CamelTodashed( array_shift($contexts) ) );
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

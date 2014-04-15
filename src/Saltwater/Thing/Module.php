<?php

namespace Saltwater\Thing;

use Saltwater\Utils as U;

/**
 * An object that can register and return providers, contexts and services
 *
 * @package Saltwater\Thing
 */
class Module
{
	public $namespace;

	protected $providers = array();

	protected $contexts = array();

	protected $services = array();

	public function provide( $type, $name, $args=null )
	{
		$class = $this->className($type, $name);

		if ( is_null($args) ) {
			return $class::get();
		} elseif ( is_array($args) ) {
			return $class::get($args[0], $args[1]);
		} else {
			return $class::get($args);
		}

		// TODO: Might be a good idea to inject the module after loading
	}

	public function providers() { return $this->provideList('providers'); }

	public function contexts() { return $this->provideList('contexts'); }

	public function services() { return $this->provideList('services'); }

	public function provideList( $type )
	{
		if ( empty($this->$type) ) return array();

		return $this->$type;
	}

	/**
	 * @param $type
	 * @param $name
	 *
	 * @return \Saltwater\Common\Provider
	 */
	protected function className( $type, $name )
	{
		return $this->namespace
			. '\\' . ucfirst($type)
			. '\\' . U::dashedToCamelCase($name);
	}
}

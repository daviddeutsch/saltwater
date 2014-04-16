<?php

namespace Saltwater\Thing;

use Saltwater\Utils as U;

/**
 * An object that can register and return providers, contexts and services
 */
class Module
{
	public $namespace;

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
	public function provide( $type, $name, $args=null )
	{
		$class = $this->className($type, $name);

		$class::setModule( U::namespacedClassToDashed(get_class($this)) );

		if ( is_null($args) ) {
			return $class::get();
		} elseif ( is_array($args) ) {
			return $class::get($args[0], $args[1]);
		} else {
			return $class::get($args);
		}
	}

	public function providers() { return $this->provideList('providers'); }

	public function contexts() { return $this->provideList('contexts'); }

	public function services() { return $this->provideList('services'); }

	public function entities() { return $this->provideList('entities'); }

	public function provideList( $type )
	{
		if ( empty($this->$type) ) return array();

		return $this->$type;
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

	public function formatModel( $name )
	{
		$name = U::snakeToCamelCase($name);

		$self = $this->namespace . '\Entity\\' . $name;

		if ( class_exists($self) ) {
			return $self;
		} elseif ( !empty($this->parent) ) {
			return $this->parent->formatModel($name);
		} else {
			return $name;
		}
	}
}

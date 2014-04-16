<?php

namespace Saltwater;

use Saltwater\Server as S;
use Saltwater\Utils as U;

class Navigator
{
	/**
	 * @var array|Thing\Module
	 */
	private $modules = array();

	/**
	 * @var array|Common\Provider
	 */
	private $providers = array();

	/**
	 * @var array|Thing\Context
	 */
	private $contexts = array();

	/**
	 * @var string
	 */
	private $root = 'root';

	/**
	 * @var string
	 */
	private $master = '';

	public function addModule( $class )
	{
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		$this->modules[$name] = new $class();

		$module =& $this->modules[$name];

		foreach ( $module->providers() as $provider ) {
			$pname = U::CamelTodashed($provider);

			if ( !isset($this->providers[$pname]) ) {
				$this->providers[$pname] = array();
			}

			$this->providers[$pname][] = $name;
		}

		foreach ( $module->contexts() as $context ) {
			$pname = U::CamelTodashed($context);

			$this->contexts[$pname] = $name;
		}

		foreach ( $module->entities() as $entity ) {
			$pname = U::CamelTodashed($entity);

			if ( !isset($this->providers[$pname]) ) {
				$this->providers[$pname] = array();
			}

			$this->providers[$pname][] = $name;
		}

		return true;
	}

	/**
	 * @param $name
	 *
	 * @return \Saltwater\Thing\Module
	 */
	public function getModule( $name )
	{
		return $this->modules[$name];
	}

	public function setRoot( $name )
	{
		$this->root = $name;
	}

	public function setMaster( $name )
	{
		$this->master = $name;
	}

	public function context( $name, $parent=null )
	{
		return $this->provide('context', array($name, $parent));
	}

	public function service( $name, $context )
	{
		return $this->provide('service', array($name, $context));
	}

	public function entity( $name, $thing=null )
	{
		return $this->provide('entity', array($name, $thing));
	}

	public function provide( $type, $args=null )
	{
		return $this->provider($type, $args);
	}

	/**
	 * @param $name
	 *
	 * @return Common\Provider
	 */
	public function provider()
	{
		$args = func_get_args();

		$type = array_shift($args);

		if ( !isset($this->providers[$type]) ) {
			S::halt(500, 'Provider does not exist: ' . $type);
		};

		foreach ( self::providerPrecedence() as $key ) {
			if ( !in_array($key, $this->providers[$type]) ) continue;

			$module = $this->getModule($key);

			return $module->provide($type, $args);
		}

		$key = array_shift( array_keys($this->providers[$type]) );

		return $this->modules[$key]->provide($type, $args);
	}

	private function providerPrecedence()
	{
		if ( $this->root == 'root' ) {
			return array($this->master, 'root');
		} else {
			return array($this->master, $this->root, 'root');
		}
	}

	public function __get( $name )
	{
		return $this->provider($name);
	}

	public function __call( $name, $args )
	{
		return $this->provider($name, $args);
	}

}

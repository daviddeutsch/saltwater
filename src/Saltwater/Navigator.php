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
	 * @var array|Thing\Provider
	 */
	private $providers = array();

	/**
	 * @var array|Thing\Context
	 */
	private $contexts = array();

	/**
	 * @var array|Thing\Entity
	 */
	private $entities = array();

	/**
	 * @var string
	 */
	private $root = 'root';

	/**
	 * @var string
	 */
	private $master = '';

	public function addModule( $class, $master=false )
	{
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		$module = new $class();

		$dependencies = $module->dependencies();

		if ( !empty($dependencies) ) {
			foreach ( $dependencies as $dependency ) {
				$this->addModule($dependency);
			}
		}

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

			if ( !isset($this->entities[$pname]) ) {
				$this->entities[$pname] = array();
			}

			$this->entities[$pname][] = $name;
		}

		$this->modules[$name] = $module;

		if ( $master ) $this->setMaster($name);

		return true;
	}

	/**
	 * @param $name
	 *
	 * @return Thing\Module
	 */
	public function getModule( $name )
	{
		return $this->modules[$name];
	}

	public function masterContext()
	{
		$module = $this->modules[$this->master];

		return $this->context( $module->masterContext() );
	}

	public function setRoot( $name )
	{
		$this->root = $name;
	}

	public function setMaster( $name )
	{
		$this->master = $name;
	}

	/**
	 * @param      $name
	 * @param null $parent
	 *
	 * @return Thing\Context
	 */
	public function context( $name, $parent=null )
	{
		return $this->provide('context', $name, $parent);
	}

	/**
	 * @param $name
	 * @param $context
	 *
	 * @return Thing\Service
	 */
	public function service( $name, $context )
	{
		return $this->provide('service', $name, $context);
	}

	/**
	 * @param      $name
	 * @param null $input
	 *
	 * @return Thing\Entity
	 */
	public function entity( $name, $input=null )
	{
		return $this->provide('entity', $name, $input);
	}

	public function provide( $type, $name, $args=null )
	{
		return $this->provider($type, $name, $args);
	}

	/**
	 * @param $name
	 *
	 * @return Thing\Provider
	 */
	public function provider()
	{
		$args = func_get_args();

		$name = array_shift($args);

		if ( $name == 'provider' ) {
			$name = array_shift($args);
		}

		if ( !isset($this->providers[$name]) ) {
			S::halt(500, 'Provider does not exist: ' . $name);
		};

		foreach ( self::providerPrecedence() as $key ) {
			if ( !in_array($key, $this->providers[$name]) ) continue;

			$module = $this->getModule($key);

			$return = $module->provide($name, $args);

			if ( $return === false ) continue;

			return $return;
		}

		$key = array_shift( array_values($this->providers[$name]) );

		return $this->modules[$key]->provide($name, $args);
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
		return $this->provider('provider', $name);
	}

	public function __call( $name, $args )
	{
		return $this->provider('provider', $name, $args);
	}

}

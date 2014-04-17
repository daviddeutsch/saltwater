<?php

namespace Saltwater;

use Saltwater\Server as S;
use Saltwater\Utils as U;

class Navigator
{
	/**
	 * @var Thing\Module[]
	 */
	private $modules = array();

	/**
	 * @var string[] array of things
	 */
	private $things = array();

	/**
	 * @var string
	 */
	private $root = 'root';

	/**
	 * @var string
	 */
	private $master = '';

	/**
	 * @var array
	 */
	private $stack = array();

	public function addModule( $class, $master=false )
	{
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		if ( isset($this->modules[$name]) ) return null;

		$this->modules[$name] = new $class();

		$this->modules[$name]->register($name);

		if ( $master ) $this->setMaster($name);

		return true;
	}

	public function isThing( $name )
	{
		return array_search($name, $this->things) !== false;
	}

	public function addThing( $name )
	{
		$id = array_search($name, $this->things);

		if ( $id ) {
			return ($id+1)^2;
		} else {
			$this->things[] = $name;

			return count($this->things)^2;
		}
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

		$context = $module->masterContext();

		if ( is_array($context) ) {
			$parent = null;
			foreach ( $context as $c ) {
				$parent = $this->context($c, $parent);
			}

			return $parent;
		} else {
			return $this->context($context);
		}
	}

	public function getContexts()
	{
		return $this->contexts;
	}

	public function setRoot( $name )
	{
		$this->root = $name;
	}

	public function setMaster( $name )
	{
		$this->master = $name;

		$this->pushStack($name);
	}

	private function pushStack( $name )
	{
		if ( empty($this->stack) ) {
			$this->stack[] = $this->root;
		}

		if ( !in_array($name, $this->stack) ) {
			$this->stack[] = $name;
		}
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

		$thing = 'provider.'.$name;

		if ( !$this->isThing($thing) ) {
			S::halt(500, 'Provider does not exist: ' . $name);
		};

		foreach ( $this->modulePrecedence() as $key ) {
			if ( !$this->modules[$key]->hasThing($thing) ) continue;

			$return = $this->modules[$key]->provide($key, $name, $args);

			if ( $return !== false ) {
				$this->setMaster($key);

				return $return;
			}
		}

		$key = array_shift( array_values($this->providers[$name]) );

		return $this->modules[$key]->provide($key, $name, $args);
	}

	private function modulePrecedence()
	{
		$return = array();
		foreach ( $this->stack as $module ) {
			array_unshift($return, $module);

			if ( $module == $this->master ) break;
		}

		return $return;
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

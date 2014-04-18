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

	public function bitThing( $name )
	{
		$id = array_search($name, $this->things);

		if ( $id === false ) {
			return false;
		} else {
			return pow(2, $id);
		}
	}

	public function addThing( $name )
	{
		$id = array_search($name, $this->things);

		if ( $id ) {
			return pow(2, $id);
		} else {
			$this->things[] = $name;

			return pow(2, count($this->things)-1);
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

	public function getContextModule( $name )
	{
		$name = 'context.' . $name;

		foreach ( $this->things as $n => $thing ) {
			if ( $thing != $name ) continue;

			$bit = pow(2, $n);

			foreach ( $this->modules as $module ) {
				if ( !$module->hasThing($bit) ) continue;

				return $module;
			}
		}

		return null;
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

		$bit = $this->bitThing('provider.' . $name);

		if ( $bit === false ) {
			S::halt(500, 'Provider does not exist: ' . $name);
		};

		$modules = $this->modulePrecedence();

		foreach ( $modules as $module ) {
			if ( !$this->modules[$module]->hasThing($bit) ) continue;

			$inject = $module;

			if ( !empty($args[0]) && is_string($args[0]) ) {
				$k = $name . '.' . $args[0];

				$m = $this->modulesByThing($k);

				if ( !empty($m) ) {
					$inject = array_pop($m);
				}
			}

			$return = $this->modules[$module]->provide($inject, $name, $args);

			if ( $return !== false ) {
				//$this->setMaster($module);

				return $return;
			}
		}

		$last = array_pop( array_values($this->stack) );

		if ( $last != $this->master ) {
			$this->setMaster($last);

			return call_user_func_array(
				array(&$this, 'provider'),
				array_merge( array($name), $args )
			);
		}

		return false;
	}

	private function modulesByThing( $thing )
	{
		if ( !$this->isThing($thing) ) return false;

		$b = $this->bitThing($thing);

		$return = array();
		foreach ( $this->modules as $k => $module ) {
			if ( $module->hasThing($b) ) {
				$return[] = $k;
			}
		}

		return $return;
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

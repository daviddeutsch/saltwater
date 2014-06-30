<?php

namespace Saltwater\Water;

use Saltwater\Salt\Module;

class ModuleStack extends MagicArrayObject
{
	/**
	 * @var TempStack
	 */
	private $stack;

	/**
	 * @var ModuleFinder
	 */
	public $finder;

	public function __construct()
	{
		$this->stack = new TempStack;

		$this->finder = new ModuleFinder;
	}

	/**
	 * Add module to stack and register its Salts
	 *
	 * @param Module|string $class  full class name
	 * @param bool          $master true if this is also the master module
	 *
	 * @return bool|null
	 */
	public function append( $class, $master=false )
	{
		if ( !$this->canAppend($class) ) return false;

		if ( !($module = $this->registerModule($class)) ) return false;

		// Push to stack after registering - to preserve order of dependencies
		$this[$class::getName()] = $module;

		if ( $master ) $this->stack->setMaster($class::getName());

		return true;
	}

	/**
	 * Check whether a module can be appended to the stack
	 *
	 * @param Module|string $class full class name
	 *
	 * @return bool
	 */
	private function canAppend( $class )
	{
		return class_exists($class) && !isset($this[$class::getName()]);
	}

	/**
	 * Return a module class by its name
	 *
	 * @param string $name
	 *
	 * @return Module|string
	 */
	public function get( $name )
	{
		return $this[$name];
	}

	/**
	 * Register a module (before adding it to the stack)
	 *
	 * @param string|Module $class
	 *
	 * @return Module
	 */
	private function registerModule( $class )
	{
		/** @var Module $module */
		$module = new $class;

		$module->register($class::getName());

		return $module;
	}

	/**
	 * @return Module[]
	 */
	public function precedenceList()
	{
		$return = array();
		foreach ( $this->stack->modulePrecedence() as $name ) {
			$return[] = $this[$name];
		}

		return $return;
	}

	public function getStack()
	{
		return $this->stack;
	}
}

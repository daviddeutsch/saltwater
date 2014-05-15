<?php

namespace Saltwater;

use Saltwater\Server as S;
use Saltwater\Utils as U;

class ModuleStack extends \ArrayObject
{
	/**
	 * @var TempStack
	 */
	private $stack;

	public function __construct()
	{
		$this->stack = new TempStack;
	}

	/**
	 * Add module to stack and register its things
	 *
	 * @param Thing\Module $class  Full Classname
	 * @param bool         $master true if this is also the master module
	 *
	 * @return bool|null
	 */
	public function appendModule( $class, $master=false )
	{
		if ( isset($this[$class::$name]) ) return null;

		if ( !($module = $this->registeredModule($class)) ) {
			return false;
		}

		// Push late to preserve dependency order
		$this[$class::$name] = $module;

		if ( $master ) $this->stack->setMaster($class::$name);

		return true;
	}

	/**
	 * Return a module class by its name
	 *
	 * @param string $name
	 *
	 * @return Thing\Module
	 */
	public function getModule( $name )
	{
		return $this[$name];
	}

	/**
	 * @param Thing\Module $class
	 *
	 * @return Thing\Module
	 */
	private function registeredModule( $class )
	{
		if ( !class_exists($class) ) return false;

		$module = $this->moduleInstance($class);

		$module->register($class::$name);

		return $module;
	}

	/**
	 * @param string $class
	 *
	 * @return Thing\Module
	 */
	private function moduleInstance( $class )
	{
		return new $class;
	}

	/**
	 * Return the master context for the current master module
	 *
	 * @param Thing\Context|null $parent inject a parent context
	 *
	 * @return Thing\Context
	 */
	public function masterContext( $parent=null )
	{
		foreach ( (array) $this as $name => $module ) {
			if ( $module->noContext() ) continue;

			$parent = S::$n->context->get($module->masterContext(), $parent);

			if ( $this->stack->isMaster($name) ) break;
		}

		return $parent;
	}

	/**
	 * @param int    $bit
	 * @param string $caller
	 * @param string $type
	 *
	 * @return bool|Thing\Provider
	 */
	public function provider( $bit, $caller, $type)
	{
		// Depending on the caller, reset the module stack
		$this->stack->setMaster($caller);

		foreach ( $this->precedenceList() as $module ) {
			$return = $this->providerFromModule($module, $bit, $caller, $type);

			if ( $return ) return $return;
		}

		return $this->tryModuleFallback($bit, $type);
	}

	/**
	 * @param Thing\Module $module
	 * @param string       $name
	 * @param int          $bit
	 * @param string       $caller
	 * @param string       $type
	 *
	 * @return bool
	 */
	private function providerFromModule( $module, $bit, $caller, $type )
	{
		if ( !$module->has($bit) ) return false;

		return $module->provider($caller, $type);
	}

	/**
	 * @param integer $bit
	 * @param string $type
	 */
	private function tryModuleFallback( $bit, $type )
	{
		// As a last resort, step one module up within stack and try again
		if ( $caller = $this->stack->advanceMaster() ) {
			return $this->provider($bit, $caller, $type);
		} else {
			return false;
		}
	}

	/**
	 * Find the module of a caller class
	 *
	 * @param array|null $caller
	 * @param string     $provider
	 *
	 * @return string module name
	 */
	public function findModule( $caller, $provider )
	{
		if ( empty($caller) ) return $this->stack->getMaster();

		$c = $this->explodeCaller($caller, $provider);

		return $this->findModuleWithCaller($c);
	}

	private function findModuleWithCaller( $c )
	{
		$bit = S::$n->bitThing($c->thing);

		foreach ( array_reverse((array) $this) as $k => $module ) {
			// A provider calling itself always gets a lower level provider
			// ($c->is_provider && $same_ns) || (!$c->is_provider && !$same_ns)
			if ( $c->is_provider === ($module->namespace == $c->namespace) ) {
				continue;
			}

			if ( $module->has($bit) ) return $k;
		}

		return null;
	}

	/**
	 * @param array  $caller
	 * @param string $provider
	 *
	 * @return object
	 */
	private function explodeCaller( $caller, $provider )
	{
		// Extract a thing from the last two particles
		$class = array_pop($caller);

		$thing = strtolower( array_pop($caller) . '.' . $class );

		// The rest is the namespace
		return (object) array(
			'thing'        => $thing,
			'namespace'    => implode('\\', $caller),
			'is_provider'  => $thing == $provider
		);
	}

	/**
	 * Return top candidate Module for providing a Thing
	 *
	 * @param string $thing
	 * @param bool   $precedence Use the current module precedence rules
	 *
	 * @return bool|mixed
	 */
	public function moduleByThing( $thing, $precedence=true )
	{
		return $this->modulesByThing($thing, $precedence, true);
	}

	/**
	 * Return a list of Modules providing a Thing
	 *
	 * @param string $thing
	 * @param bool   $precedence
	 * @param bool   $first      only return the first item on the list
	 *
	 * @return array|bool
	 */
	public function modulesByThing( $thing, $precedence=true, $first=false )
	{
		if ( !S::$n->isThing($thing) ) return false;

		$call = $first ? 'getThingModule' : 'getThingModules';

		return $this->$call(
			$this->getPrecedence($precedence),
			S::$n->bitThing($thing)
		);
	}

	public function getThingModules( $bit, $modules=null )
	{
		$modules = is_null($modules) ? array_keys((array) $this) : $modules;

		$return = array();
		foreach ( $modules as $module ) {
			if ( !$this[$module]->hasThing($bit) ) continue;

			$return[] = $module;
		}

		return $return;
	}

	public function getThingModule( $bit, $modules=null )
	{
		$modules = is_null($modules) ? array_keys((array) $this) : $modules;

		foreach ( $modules as $module ) {
			if ( $this[$module]->hasThing($bit) ) return $module;
		}

		return false;
	}

	private function getPrecedence( $stack_precedence )
	{
		if ( $stack_precedence ) {
			return $this->stack->modulePrecedence();
		} else {
			return array_keys( array_reverse((array) $this) );
		}
	}

	private function precedenceList()
	{
		$return = array();
		foreach ( $this->stack->modulePrecedence() as $name ) {
			$return[] = $this[$name];
		}

		return $return;
	}

	public function __sleep()
	{
		foreach ( (array) $this as $k => $v ) {
			$this[$k] = array(
				'class' => get_class($v),
				'registry' => $v->registry
			);
		}
	}

	public function __wakeup()
	{
		foreach ( (array) $this as $k => $v ) {
			$class = $v['class'];

			$this[$k] = new $class;
			$this[$k]->registry = $v['registry'];
		}
	}
}

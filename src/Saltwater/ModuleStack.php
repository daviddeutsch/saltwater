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

	/**
	 * Add module to stack and register its things
	 *
	 * @param string $class  Full Classname
	 * @param bool   $master true if this is also the master module
	 *
	 * @return bool|null
	 */
	public function appendModule( $class, $master=false )
	{
		$name = U::namespacedClassToDashed($class);

		if ( isset($this[$name]) ) return null;

		if ( !($module = $this->registeredModule($class, $name)) ) {
			return false;
		}

		// Push late to preserve dependency order
		$this[$name] = $module;

		if ( $master ) $this->stack->setMaster($name);

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
	 * @param string $class
	 *
	 * @return Thing\Module
	 */
	private function registeredModule( $class, $name )
	{
		if ( !class_exists($class) ) return false;

		$module = $this->moduleInstance($class);

		$module->register($name);

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
	 * Get the Module that provides a context
	 *
	 * @param string $name plain name of the context
	 *
	 * @return Thing\Module|null
	 */
	public function getContextModule( $name )
	{
		$bit = S::$n->bitThing('context.' . $name);

		foreach ( (array) $this as $module ) {
			if ( $module->hasThing($bit) ) return $module;
		}

		return null;
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

		foreach ( $this->stack->modulePrecedence() as $name ) {
			$return = $this->providerFromModule(
				$this[$name], $name, $bit, $caller, $type
			);

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
	private function providerFromModule( $module, $name, $bit, $caller, $type )
	{
		if ( !$module->has($bit) ) return false;

		return $module->provider($name, $caller, $type);
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
		if ( empty($caller) ) return null;

		$caller = $this->explodeCaller($caller, $provider);

		$bit = S::$n->bitThing($caller->thing);

		foreach ( array_reverse((array) $this) as $k => $module ) {
			if ( !$this->bitInModule($module, $caller, $bit) ) continue;

			return $k;
		}

		return null;
	}

	/**
	 * @param Thing\Module $module
	 * @param object       $c
	 * @param string       $bit
	 *
	 * @return bool
	 */
	private function bitInModule( $module, $c, $bit )
	{
		// A provider calling itself always gets a lower level provider
		// ($c->is_provider && $same_ns) || (!$c->is_provider && !$same_ns)
		if ( $c->is_provider === ($module->namespace == $c->namespace) ) {
			return false;
		}

		return $module->has($bit);
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
	 * @param      string $thing
	 * @param bool $precedence
	 * @param bool $first      only return the first item on the list
	 *
	 * @return array|bool
	 */
	public function modulesByThing( $thing, $precedence=true, $first=false )
	{
		if ( !S::$n->isThing($thing) ) return false;

		if ( $precedence ) {
			$modules = $this->stack->modulePrecedence();
		} else {
			$modules = array_keys( array_reverse((array) $this) );
		}

		$bit = S::$n->bitThing($thing);

		return $this->thingInList($modules, $bit, $first);
	}

	/**
	 * @param boolean $first
	 */
	private function thingInList( $modules, $bit, $first=false )
	{
		$return = array();
		foreach ( $modules as $module ) {
			if ( !$this[$module]->hasThing($bit) ) continue;

			if ( $first ) return $module;

			$return[] = $module;
		}

		return $return;
	}

	public function __sleep()
	{
		foreach ( (array) $this as $k => $v ) {
			$this[$k] = array(
				'class' => get_class($v),
				'things' => $v->things
			);
		}
	}

	public function __wakeup()
	{
		foreach ( (array) $this as $k => $v ) {
			$class = $v['class'];

			$this[$k] = new $class;
			$this[$k]->things = $v['things'];
		}
	}
}

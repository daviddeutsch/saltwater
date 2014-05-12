<?php

namespace Saltwater;

use Saltwater\Server as S;
use Saltwater\Utils as U;

class ModuleStack extends \ArrayObject
{
	/**
	 * @var string
	 */
	private $root = 'root';

	/**
	 * @var string
	 */
	private $master = '';

	/**
	 * @var string[] array of modules stacked in the order they were called in
	 */
	private $stack = array();

	/**
	 * Set the root module by name
	 *
	 * @param string $name
	 */
	public function setRoot( $name )
	{
		if ( empty($name) || ($name == $this->root) ) return;

		$this->root = $name;
	}

	/**
	 * Set the master module by name
	 *
	 * @param string $name
	 */
	public function setMaster( $name )
	{
		if ( empty($name) || ($name == $this->master) ) return;

		$this->master = $name;

		$this->pushStack($name);
	}

	/**
	 * Push a module name onto the stack, establishing later hierarchy for calls
	 *
	 * @param string $name
	 */
	private function pushStack( $name )
	{
		if ( empty($this->stack) ) $this->stack[] = $this->root;

		if ( in_array($name, $this->stack) ) return;

		$this->stack[] = $name;
	}

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
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		if ( isset($this[$name]) ) return null;

		$module = $this->moduleInstance($class);

		$module->register($name);

		// Push to ->modules late to preserve dependency order
		$this[$name] = $module;

		if ( $master ) $this->setMaster($name);

		return true;
	}

	/**
	 * @param $class
	 *
	 * @return \Saltwater\Thing\Module
	 */
	private function moduleInstance( $class )
	{
		return new $class();
	}

	/**
	 * Return the master context for the current master module
	 *
	 * @param \Saltwater\Thing\Context|null $parent inject a parent context
	 *
	 * @return \Saltwater\Thing\Context
	 */
	public function masterContext( $parent=null )
	{
		foreach ( (array) $this as $name => $module ) {
			$context = $module->masterContext();

			if ( !empty($context) ) {
				$parent = S::$n->context->get($context, $parent);
			}

			if ( $name == $this->master ) break;
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
	public function providerFromModule( $bit, $caller, $type)
	{
		// Depending on the caller, reset the module stack
		$this->setMaster($caller);

		foreach ( $this->modulePrecedence() as $k ) {
			if ( !$this[$k]->hasThing($bit) ) continue;

			$return = $this[$k]->provider($k, $caller, $type);

			if ( $return !== false ) return $return;
		}

		return $this->tryModuleFallback($bit, $type);
	}

	private function tryModuleFallback( $bit, $type )
	{
		$master = array_search($this->master, $this->stack);

		if ( $master == (count($this->stack) - 1) ) return false;

		// As a last resort, step one module up within stack and try again
		$caller = $this->stack[$master+1];

		return $this->providerFromModule($bit, $caller, $type);
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

		return $module->hasThing($bit);
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
			$modules = $this->modulePrecedence();
		} else {
			$modules = array_keys( array_reverse((array) $this) );
		}

		$bit = S::$n->bitThing($thing);

		return $this->thingInStack($modules, $bit, $first);
	}

	private function thingInStack( $modules, $bit, $first )
	{
		$return = array();
		foreach ( $modules as $module ) {
			if ( !$this[$module]->hasThing($bit) ) continue;

			if ( $first ) return $module;

			$return[] = $module;
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

			$this[$k] = new $class();
			$this[$k]->things = $v['things'];
		}
	}
}

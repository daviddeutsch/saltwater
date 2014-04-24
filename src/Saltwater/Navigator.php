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
	 * @var string[] array of Saltwater\Thing(s)
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
	 * @var string[] array of modules stacked in the order they were called in
	 */
	private $stack = array();

	/**
	 * Add module to Navigator and register its things
	 *
	 * @param string $class  Full Classname
	 * @param bool   $master true if this is also the master module
	 *
	 * @return bool|null
	 */
	public function addModule( $class, $master=false )
	{
		if ( !class_exists($class) ) return false;

		$name = U::namespacedClassToDashed($class);

		if ( isset($this->modules[$name]) ) return null;

		$module = new $class();

		$module->register($name);

		$this->modules[$name] = $module;

		if ( $master ) $this->setMaster($name);

		return true;
	}

	/**
	 * Return true if the input is a registered thing
	 *
	 * @param string $name in the form "type.name"
	 *
	 * @return bool
	 */
	public function isThing( $name )
	{
		return array_search($name, $this->things) !== false;
	}

	/**
	 * Return the bitmask integer of a thing
	 *
	 * @param string $name in the form "type.name"
	 *
	 * @return bool|int
	 */
	public function bitThing( $name )
	{
		$id = array_search($name, $this->things);

		if ( $id === false ) {
			return false;
		} else {
			return pow(2, $id);
		}
	}

	/**
	 * Register a thing and return its bitmask integer
	 * @param $name
	 *
	 * @return number
	 */
	public function addThing( $name )
	{
		$id = array_search($name, $this->things);

		if ( !$id ) {
			$id = count($this->things);

			$this->things[] = $name;
		}

		return pow(2, $id);
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
		return $this->modules[$name];
	}

	/**
	 * Return the master context for the current master module
	 *
	 * @return null|Thing\Context
	 */
	public function masterContext()
	{
		$context = $this->modules[$this->master]->masterContext();

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

	/**
	 * Get the Module that provides a context
	 *
	 * @param string $name plain name of the context
	 *
	 * @return null|Thing\Module
	 */
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

	/**
	 * Set the root module by name
	 *
	 * @param string $name
	 */
	public function setRoot( $name )
	{
		$this->root = $name;
	}

	/**
	 * Set the master module by name
	 *
	 * @param string $name
	 */
	public function setMaster( $name )
	{
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
		if ( empty($this->stack) ) {
			$this->stack[] = $this->root;
		}

		if ( !in_array($name, $this->stack) ) {
			$this->stack[] = $name;
		}
	}

	/**
	 * Provide a Thing\Context by name, possibly injecting a parent context
	 *
	 * @param string             $name
	 * @param null|Thing\Context $parent
	 *
	 * @return Thing\Context
	 */
	public function context( $name, $parent=null )
	{
		return $this->provide('context', $name, $parent);
	}

	/**
	 * Provide a Thing\Service by name, specifying for which context
	 *
	 * @param string        $name
	 * @param Thing\Context $context
	 *
	 * @return Thing\Service
	 */
	public function service( $name, $context )
	{
		return $this->provide('service', $name, $context);
	}

	/**
	 * Provide a Thing\Entity or Thing\Association by name
	 *
	 * @param string $name
	 * @param null   $input
	 *
	 * @return Thing\Entity
	 */
	public function entity( $name, $input=null )
	{
		return $this->provide('entity', $name, $input);
	}

	/**
	 * Generic call for a provider, specifying type and name, relaying arguments
	 *
	 * @param string     $type
	 * @param string     $name
	 * @param array|null $args
	 *
	 * @return Thing\*
	 */
	public function provide( $type, $name, $args=null )
	{
		return $this->provider($type, $name, $args);
	}

	/**
	 * Call upon a provider by type and name, relaying arguments
	 *
	 * @return Thing\Provider
	 */
	public function provider()
	{
		$args = func_get_args();

		$name = array_shift($args);

		$provider = 'provider.' . $name;

		$bit = $this->bitThing($provider);

		if ( $bit === false ) {
			S::halt(500, 'Provider does not exist: ' . $name);
		};

		$caller = $this->findCallerModule($provider);

		if ( $caller ) {
			$this->setMaster($caller);
		}

		$modules = $this->modulePrecedence();

		foreach ( $modules as $module ) {
			if ( !$this->modules[$module]->hasThing($bit) ) {

				continue;
			}

			$inject = $module;

			if ( !empty($args[0]) && is_string($args[0]) ) {
				$k = $name . '.' . $args[0];

				$m = $this->moduleByThing($k);

				if ( !empty($m) ) $inject = $m;
			}

			$return = $this->modules[$module]->provide($inject, $name, $args);

			if ( $return !== false ) return $return;
		}

		$master = array_search($this->master, $this->stack);

		// As a last resort, step one module out of the master and try again
		if ( $master != count($this->stack)-1 ) {
			$this->setMaster($this->stack[$master+1]);

			return call_user_func_array(
				array(&$this, 'provider'),
				array_merge( array($name), $args )
			);
		}

		return false;
	}

	/**
	 * Since I understand this will raise heads, allow me to explain:
	 *
	 * Doing the backtrace and getting the recent caller module takes between
	 * 14 and 25 microseconds, with a median of 18 microseconds.
	 *
	 * The only other option to provide the same infrastructure would be to
	 * introduce inheritance logic right into the calling object stack.
	 * My feeling is that it's just not worth it - the inheritance logic would
	 * probably add around the same overhead while recreating functionality
	 * that PHP already provides with debug_backtrace().
	 *
	 * It would take 50+ calls to add even a millisecond delay to a request,
	 * about twice the typical amount of calls in a saltwater query.
	 *
	 * One of the two should be used, though. Not only do we need to establish
	 * a context, but that (plus either of those options) makes call caching
	 * possible, again reducing the number of actual calls happening after the
	 * check, which would be more expensive than the backtrace itself.
	 *
	 * All in all, I'd say it's a wash. However, in my opinion, solving
	 * the problem with a couple dozen lines is preferable to setting up an
	 * entire architecture which, again, only imitates existing functionality.
	 *
	 * "It does feel a little dirty, but as has been
	 * well documented, opined, and beaten to death elsewhere,
	 * PHP isn't a system designed for elegance."
	 *
	 *                                - http://stackoverflow.com/a/347014
	 *
	 * @return string Class name
	 */
	private function findCallerModule( $provider )
	{
		// Let me tell you about my boat
		$trace = debug_backtrace(2, 8);

		$depth = count($trace);

		for ( $i=2; $i<$depth; ++$i ) {
			if ( !isset($trace[$i]['class']) ) continue;

			if (
				( $trace[$i]['class'] == 'Saltwater\Navigator' )
				|| ( $trace[$i]['class'] == 'Saltwater\Server' )
			) continue;

			$class = $trace[$i]['class']; break;
		}

		if ( empty($class) ) return false;

		$caller = explode('\\', $class);

		$thing = array_pop($caller);

		$thing = strtolower( array_pop($caller) . '.' . $thing );

		$namespace = implode('\\', $caller);

		$is_provider = $thing == $provider;

		$bit = 0;
		if ( $is_provider ) {
			$bit = $this->bitThing($thing);
		}

		// Make it possible for providers to call "themselves"
		// (actually: providers of the same name higher up the chain)
		foreach ( array_reverse($this->modules) as $k => $module ) {
			if ( $is_provider ) {
				if ( !$module->hasThing($bit) ) continue;

				if ( $module->namespace == $namespace ) continue;
			} elseif ( $module->namespace !== $namespace ) {
				continue;
			}

			return $k;
		}

		return null;
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
		$modules = $this->modulesByThing($thing, $precedence);

		if ( empty($modules) ) return false;

		return array_shift($modules);
	}

	/**
	 * Return a list of Modules providing a Thing
	 * @param      $thing
	 * @param bool $precedence
	 *
	 * @return array|bool
	 */
	public function modulesByThing( $thing, $precedence=true )
	{
		if ( !$this->isThing($thing) ) return false;

		$b = $this->bitThing($thing);

		if ( $precedence ) {
			$modules = $this->modulePrecedence();
		} else {
			$modules = array_reverse( array_keys($this->modules) );
		}

		$return = array();
		foreach ( $modules as $module ) {
			if ( $this->modules[$module]->hasThing($b) ) {
				$return[] = $module;
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

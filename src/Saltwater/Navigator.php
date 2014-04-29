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
		return in_array($name, $this->things) !== false;
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
		return array_search($name, $this->things);
	}

	/**
	 * Register a thing and return its bitmask integer
	 * @param $name
	 *
	 * @return number
	 */
	public function addThing( $name )
	{
		$id = $this->bitThing($name);

		if ( $id ) return $id;

		$id = pow( 2, count($this->things) );

		$this->things[$id] = $name;

		return $id;
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
		}

		return $this->context($context);
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

			foreach ( $this->modules as $module ) {
				if ( !$module->hasThing($n) ) continue;

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
		return $this->factory('context', $name, $parent);
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
		return $this->factory('service', $name, $context);
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
		return $this->factory('entity', $name, $input);
	}

	/**
	 * Generic call for a factory, specifying type and name, relaying arguments
	 *
	 * @param string     $type
	 * @param string     $name
	 * @param array|null $args
	 *
	 * @return Thing\*
	 */
	public function factory( $type, $name, $args=array() )
	{
		return $this->get('factory', $type, $name, $args);
	}

	/**
	 * Generic call for a type of provider
	 *
	 * @param string $type
	 *
	 * @return Thing\Provider
	 */
	public function provider( $type )
	{
		return $this->get('provider', $type);
	}

	/**
	 * Call upon a provider or factory by type and name, relaying arguments
	 *
	 * @param string $base either 'factory' or 'provider'
	 * @param string $type specifies what type of factory or provider
	 * @param string $name Factories need a name
	 * @param array  $args Factories can accept further arguments
	 *
	 * @return Thing\*
	 */
	protected function get( $base, $type, $name=null, $args=array() )
	{
		$what = $base . '.' . $type;

		$bit = $this->bitThing($what);

		if ( $bit === false ) {
			S::halt(500, ucfirst($base) . ' does not exist: ' . $type);
		};

		$this->setMaster( $this->findCallerModule($what) );

		foreach ( $this->modulePrecedence() as $k ) {
			$module = $this->modules[$k];

			if ( !$module->hasThing($bit) ) continue;

			$inject = $k;

			if ( $base == 'provider' ) {
				$return = $module->$base($inject, $type);
			} else {
				$m = $this->moduleByThing($type . '.' . $name);

				if ( !empty($m) ) $inject = $m;

				$return = $module->$base($inject, $type, $name, $args);
			}

			if ( $return !== false ) return $return;
		}

		$master = array_search($this->master, $this->stack);

		// As a last resort, step one module out of the stack and try again
		if ( $master == count($this->stack)-1 ) return false;

		$this->setMaster($this->stack[$master+1]);

		if ( $base == 'provider' ) {
			return call_user_func( array(&$this, $base), $type );
		} else {
			return call_user_func_array(
				array(&$this, 'factory'),
				array($type, $name, $args)
			);
		}
	}

	/**
	 * Use debug_backtrace() to find the caller module, for details, check:
	 *
	 * https://github.com/daviddeutsch/saltwater/wiki/on-using-debug_backtrace
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

	public function __get( $type )
	{
		return $this->provider($type);
	}

	public function __call( $type, $args )
	{
		if ( !empty($args) ) {
			$name = array_shift($args);
		} else {
			$name = null;
		}

		return $this->factory($type, $name, $args);
	}

}

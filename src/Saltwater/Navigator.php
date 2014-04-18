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
			if ( !$this->modules[$module]->hasThing($bit) ) continue;

			$inject = $module;

			if ( !empty($args[0]) && is_string($args[0]) ) {
				$k = $name . '.' . $args[0];

				$m = $this->moduleByThing($k);

				if ( !empty($m) ) $inject = $m;
			}

			$return = $this->modules[$module]->provide($inject, $name, $args);

			if ( $return !== false ) return $return;
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
		$trace = debug_backtrace(false, 7);

		$depth = count($trace);

		for ( $i=2; $i<$depth; ++$i ) {
			if ( $trace[$i]['class'] == 'Saltwater\Navigator' ) continue;

			$class = $trace[$i]['class']; break;
		}

		if ( empty($class) ) return false;

		$caller = explode('\\', $class);

		$thing = array_pop($caller);

		$thing = strtolower( $thing . '.' . array_pop($caller) );

		$namespace = implode('\\', $caller);

		$is_provider = $thing == $provider;

		$bit = 0;
		if ( $is_provider ) {
			$bit = $this->bitThing($thing);
		}

		foreach ( array_reverse($this->modules) as $k => $module ) {
			if ( $is_provider ) {
				if ( $module->hasThing($bit) ) {
					if ( $module->namespace == $namespace ) continue;
				} else {
					continue;
				}
			} elseif ( $module->namespace !== $namespace ) {
				continue;
			}

			return $k;
		}

		return null;
	}

	public function moduleByThing( $thing, $precedence=true )
	{
		$modules = $this->modulesByThing($thing, $precedence);

		if ( empty($modules) ) return false;

		return array_pop($modules);
	}

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

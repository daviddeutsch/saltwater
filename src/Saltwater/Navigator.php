<?php

namespace Saltwater;

use Saltwater\Server as S;
use Saltwater\Utils as U;

/**
 * Class Navigator
 *
 * @package Saltwater
 *
 * List of known providers:
 *
 * @property \Saltwater\Root\Provider\Context $context
 * @property \Saltwater\Root\Provider\Entity  $entity
 * @property \Saltwater\Root\Provider\Service $service
 *
 * @property \RedBean_Instance $db
 *
 * @property \Saltwater\Root\Provider\Log      $log
 * @property \Saltwater\Root\Provider\Response $response
 * @property \Saltwater\Root\Provider\Route    $route
 *
 * @property \Saltwater\Common\Config $config
 */
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
	 * @var array classes that can be skipped during search for caller module
	 */
	private $skip = array(
		'Saltwater\Navigator',
		'Saltwater\Server'
	);

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

		// Push to ->modules late to preserve dependency order
		$this->modules[$name] = $module;

		if ( $master ) $this->setMaster($name);

		return true;
	}

	/**
	 * @param string $path
	 */
	public function cache( $path )
	{
		$cache = array();
		foreach ( array('things', 'root', 'master', 'stack') as $k ) {
			$cache[$k] = $this->$k;
		}

		$cache['modules'] = array();
		foreach ( $this->modules as $name => $module ) {
			$cache['modules'][$name] = get_class($module);

			$cache['bits'][$name] = $module->things;
		}

		$info = pathinfo($path);

		if ( !is_dir($info['dirname']) ) mkdir($info['dirname'], 0744, true);

		file_put_contents( $path, serialize($cache)	);
	}

	/**
	 * @param string $path
	 */
	public function loadCache( $path )
	{
		$cache = unserialize( file_get_contents($path) );

		foreach ( $cache['modules'] as $name => $module ) {
			$this->modules[$name] = new $module();

			$this->modules[$name]->things = $cache['bits'][$name];
		}

		foreach ( array('things', 'root', 'master', 'stack') as $k ) {
			$this->$k = $cache[$k];
		}
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
		if ( $id = $this->bitThing($name) ) return $id;

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
	 * @param Thing\Context $parent inject a parent context
	 *
	 * @return Thing\Context
	 */
	public function masterContext( $parent=null )
	{
		foreach ( $this->modules as $name => $module ) {
			$context = $module->masterContext();

			if ( !empty($context) ) {
				$parent = $this->context->get($context, $parent);
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
	 * @return Thing\Module
	 */
	public function getContextModule( $name )
	{
		$name = 'context.' . $name;

		foreach ( $this->things as $n => $thing ) {
			if ( $thing != $name ) continue;

			foreach ( $this->modules as $module ) {
				if ( $module->hasThing($n) ) return $module;
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
		if ( empty($this->stack) ) $this->stack[] = $this->root;

		if ( in_array($name, $this->stack) ) return;

		$this->stack[] = $name;
	}

	/**
	 * Generic call for a type of provider
	 *
	 * @param string $type
	 * @param string $caller Caller module name
	 *
	 * @return Thing\Provider
	 */
	public function provider( $type, $caller=null )
	{
		$thing = 'provider.' . $type;

		if ( !$bit = $this->bitThing($thing) ) {
			S::halt(500, 'provider does not exist: ' . $type);
		};

		if ( empty($caller) ) {
			$caller = $this->findModule($this->lastCaller(), $thing);
		}

		// Depending on the caller, reset the module stack
		$this->setMaster($caller);

		foreach ( $this->modulePrecedence() as $k ) {
			if ( !$this->modules[$k]->hasThing($bit) ) continue;

			$return = $this->modules[$k]->provider($k, $caller, $type);

			if ( $return !== false ) return $return;
		}

		$master = array_search($this->master, $this->stack);

		if ( $master == count($this->stack)-1 ) return false;

		// As a last resort, step one module up within stack and try again
		$this->setMaster($this->stack[$master+1]);

		return call_user_func( array(&$this, 'provider'), $type );
	}

	/**
	 * Find the module of a caller class
	 *
	 * @param string $provider
	 * @return string module name
	 */
	private function findModule( $caller, $provider )
	{
		if ( empty($caller) ) return null;

		// Extract a thing from the last two particles
		$thing = array_pop($caller);

		$thing = strtolower( array_pop($caller) . '.' . $thing );

		// Everything else is Namespace
		$namespace = implode('\\', $caller);

		// Check whether this is a provider calling "itself"
		$is_provider = $thing == $provider;

		$bit = $is_provider ? $this->bitThing($thing) : 0;

		foreach ( array_reverse($this->modules) as $k => $module ) {
			// A provider calling itself always gets a lower level provider
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
	 * Extracts the last calling class from a debug_backtrace, skipping the
	 * Navigator and Server, of course.
	 *
	 * And - Yup, debug_backtrace().
	 *
	 * @return string class name
	 */
	public function lastCaller()
	{
		// Let me tell you about my boat
		$trace = debug_backtrace(2, 8);

		$depth = count($trace);

		// Iterate through backtrace, find the last caller class
		for ( $i=2; $i<$depth; ++$i ) {
			if ( !isset($trace[$i]['class']) ) continue;

			if ( in_array($trace[$i]['class'], $this->skip) ) continue;

			if ( strpos($trace[$i]['class'], 'Saltwater\Root') !== false ) continue;

			return explode('\\', $trace[$i]['class']);
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
		if ( !$this->isThing($thing) ) return false;

		$b = $this->bitThing($thing);

		if ( $precedence ) {
			$modules = $this->modulePrecedence();
		} else {
			$modules = array_reverse( array_keys($this->modules) );
		}

		$return = array();
		foreach ( $modules as $module ) {
			if ( !$this->modules[$module]->hasThing($b) ) continue;

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

	public function __get( $type )
	{
		return $this->provider($type);
	}

	public function __call( $type, $args )
	{
		$caller = empty($args) ? null : array_shift($args);

		return $this->provider($type, $caller);
	}
}

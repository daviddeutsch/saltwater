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
	 * @var ModuleStack
	 */
	private $modules = array();

	/**
	 * @var string[] array of Saltwater\Thing(s)
	 */
	private $things = array();

	/**
	 * @var array classes that can be skipped during search for caller module
	 */
	private $skip = array(
		'Saltwater\Navigator',
		'Saltwater\Server'
	);

	public function __construct()
	{
		$this->modules = new ModuleStack();
	}

	/**
	 * @see ModuleStack::appendModule()
	 */
	public function addModule( $class, $master=false )
	{
		return $this->modules->appendModule($class, $master);
	}

	/**
	 * @param string $path
	 */
	public function storeCache( $path )
	{
		$info = pathinfo($path);

		if ( !is_dir($info['dirname']) ) mkdir($info['dirname'], 0744, true);

		file_put_contents( $path, serialize($this) );
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function loadCache( $path )
	{
		$cache = unserialize( file_get_contents($path) );

		foreach ( $cache as $k => $v ) {
			$this->$k = $v;
		}

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
	 * @see ModuleStack::masterContext()
	 */
	public function masterContext( $parent=null )
	{
		return $this->modules->masterContext($parent);
	}

	/**
	 * @see ModuleStack::getContextModule()
	 */
	public function getContextModule( $name )
	{
		return $this->modules->getContextModule($name);
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
			$caller = $this->modules->findModule($this->lastCaller(), $thing);
		}

		return $this->modules->providerFromModule($bit, $caller, $type);
	}

	/**
	 * Extracts the last calling class from a debug_backtrace, skipping the
	 * Navigator and Server, of course.
	 *
	 * And - Yup, debug_backtrace().
	 *
	 * @return array|null
	 */
	public function lastCaller()
	{
		// Let me tell you about my boat
		$trace = debug_backtrace(2, 22);

		$depth = count($trace);

		// Iterate through backtrace, find the last caller class
		for ( $i=2; $i<$depth; ++$i ) {
			if ( !isset($trace[$i]['class']) ) continue;

			if ( $this->skipCaller($trace[$i]['class']) ) continue;

			return explode('\\', $trace[$i]['class']);
		}

		return null;
	}

	private function skipCaller( $class )
	{
		return (strpos($class, 'Saltwater\Root') !== false)
			|| (strpos($class, '\\') === false)
			|| in_array($class, $this->skip);
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

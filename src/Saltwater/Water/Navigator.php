<?php

namespace Saltwater\Water;

use Saltwater\Server as S;
use Saltwater\Salt\Module;
use Saltwater\Salt\Provider;

/**
 * Class Navigator
 *
 * @package Saltwater
 *
 * List of known providers:
 *
 * @property \Saltwater\Root\Provider\Context    $context
 * @property \Saltwater\RedBean\Provider\Entity  $entity
 * @property \Saltwater\Root\Provider\Service    $service
 *
 * @property \RedBean_Instance $db
 *
 * @property \Saltwater\RedBean\Provider\Log  $log
 * @property \Saltwater\App\Provider\Response $response
 * @property \Saltwater\App\Provider\Route    $route
 *
 * @property \Saltwater\App\Common\Config $config
 *
 * Same as the above, but as methods for injecting a caller
 *
 * @method \Saltwater\Root\Provider\Context    context( $caller=null )
 * @method \Saltwater\RedBean\Provider\Entity  entity( $caller=null )
 * @method \Saltwater\Root\Provider\Service    service( $caller=null )
 *
 * @method \RedBean_Instance db( $caller=null )
 *
 * @method \Saltwater\RedBean\Provider\Log  log( $caller=null )
 * @method \Saltwater\App\Provider\Response response( $caller=null )
 * @method \Saltwater\App\Provider\Route    route( $caller=null )
 *
 * @method \Saltwater\App\Common\Config config( $caller=null )
 */
class Navigator
{
	/** @var ModuleStack */
	private $modules = array();

	/** @var Registry */
	private $registry = array();

	/**
	 * Initiate the navigator by setting up a ModuleStack and a Registry
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->modules = new ModuleStack();

		$this->registry = new Registry();
	}

	/**
	 * Magic property to get a provider without a preset caller
	 *
	 * @param string $type
	 *
	 * @return Provider
	 */
	public function __get( $type )
	{
		return $this->provider($type);
	}

	/**
	 * Magic function to get a provider with a preset caller
	 *
	 * @param string $type
	 * @param mixed  $args
	 *
	 * @return Provider
	 */
	public function __call( $type, $args )
	{
		$caller = empty($args) ? null : array_shift($args);

		return $this->provider($type, $caller);
	}

	/**
	 * Store the navigator within a cache file
	 *
	 * @param string $path
	 */
	public function storeCache( $path )
	{
		$info = pathinfo($path);

		if ( !is_dir($info['dirname']) ) mkdir($info['dirname'], 0744, true);

		return file_put_contents( $path, serialize($this) );
	}

	/**
	 * Recreate the navigator from a cache file
	 *
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
	 * @see Registry::exists()
	 */
	public function isSalt( $name )
	{
		return $this->registry->exists($name);
	}

	/**
	 * @see Registry::bit()
	 */
	public function bitSalt( $name )
	{
		return $this->registry->bit($name);
	}

	/**
	 * @see Registry::append()
	 */
	public function addSalt( $name )
	{
		return $this->registry->append($name);
	}

	/**
	 * @see ModuleStack::appendModule()
	 */
	public function addModule( $class, $master=false )
	{
		return $this->modules->appendModule($class, $master);
	}

	/**
	 * @see ModuleStack::getModule()
	 */
	public function getModule( $name )
	{
		return $this->modules->getModule($name);
	}

	/**
	 * @see ModuleStack::findModule()
	 */
	public function findModule( $caller, $provider )
	{
		return $this->modules->findModule($caller, $provider);
	}

	/**
	 * @see ModuleStack::moduleBySalt()
	 */
	public function moduleBySalt( $name )
	{
		return $this->modules->moduleBySalt($name);
	}

	/**
	 * @see ModuleStack::masterContext()
	 */
	public function masterContext( $parent=null )
	{
		return $this->modules->masterContext($parent);
	}

	/**
	 * Get the Module that provides a context
	 *
	 * @param string $name plain name of the context
	 *
	 * @return Module|null
	 */
	public function getContextModule( $name )
	{
		return $this->getModule(
			$this->modules->getSaltModule(
				S::$n->bitSalt('context.' . $name)
			)
		);
	}

	/**
	 * Generic call for a type of provider
	 *
	 * @param string $type
	 * @param string $caller Caller module name
	 *
	 * @return Provider
	 */
	public function provider( $type, $caller=null )
	{
		$salt = 'provider.' . $type;

		if ( !$bit = S::$n->bitSalt($salt) ) {
			S::halt(500, 'provider does not exist: ' . $type);
		};

		if ( empty($caller) ) {
			$caller = S::$n->findModule(Backtrace::lastCaller(), $salt);
		}

		return $this->modules->provider($bit, $caller, $type);
	}
}

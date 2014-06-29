<?php

namespace Saltwater\Water;

use Saltwater\Server as S;
use Saltwater\Salt\Module;
use Saltwater\Salt\Provider;

/**
 * Class Navigator
 *
 * @package Saltwater\Water
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
	public $modules = array();

	/** @var Registry */
	public $registry = array();

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
	 * Get the Module that provides a context
	 *
	 * @param string $name plain name of the context
	 *
	 * @return Module|null
	 */
	public function getContextModule( $name )
	{
		return $this->modules->get(
			$this->modules->finder->getSaltModule(
				$this->registry->bit('context.' . $name)
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

		if ( !$bit = $this->registry->bit($salt) ) {
			S::halt(500, 'provider does not exist: ' . $type);
		};

		if ( empty($caller) ) {
			$caller = $this->modules->finder->find(Backtrace::lastCaller(), $salt);
		}

		return $this->modules->finder->provider($bit, $caller, $type);
	}
}

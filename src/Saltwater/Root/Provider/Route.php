<?php

namespace Saltwater\Root\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\Common\Route as AbstractRoute;

class Route extends AbstractRoute
{
	protected function __construct()
	{
		$this->uri = $this->getURI();

		$this->http = $this->getHTTP();

		$context = S::$n->masterContext();

		if ( empty($context) ) return;

		$this->explode(
			$context,
			$this->http,
			explode('/', $this->uri)
		);
	}

	public static function getProvider()
	{
		$module = S::$n->getModule(self::$module);

		$class = $module->namespace . '\Provider\Route';

		return new $class();
	}

	/**
	 * Get the URI from $_SERVER
	 *
	 * @return string
	 */
	protected function getURI()
	{
		return $this->filterURI(
			$_SERVER['REQUEST_URI'],
			$_SERVER['SCRIPT_NAME']
		);
	}

	private function filterURI( $uri, $path )
	{
		if ( strpos($uri, $path) === false ) {
			$path = str_replace( '\\', '', dirname($path) );
		}

		$path = substr_replace( $uri, '', 0, strlen($path) );

		if ( isset($_SERVER['REQUEST_URI']) ) {
			$path = str_replace('?' . $_SERVER['REQUEST_URI'], '', $path);
		}

		$path = preg_replace('`[^a-z0-9/._-]+`', '', strtolower($path));

		if ( strpos($path, '.zip') ) {
			$path = str_replace('.zip', '', $path);
		}

		return $path;
	}

	protected function getHTTP()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	public function go()
	{
		$input = @file_get_contents('php://input');

		if ( !$input ) $input = '';

		S::$n->response->response( $this->resolveChain($input) );
	}

	private function resolveChain( $input, $result=null )
	{
		$input = empty($input) ? null : json_decode($input);

		$length = count($this->chain) - 1;

		$service = new \Saltwater\Thing\Service();
		for ( $i=0; $i<$length; ++$i ) {
			$c =& $this->chain[$i];

			$c->context->pushData($result);

			$service->setContext($c->context);

			if ( !$service->prepareCall($c) ) {
				$service = S::$n->service->get($c->service, $c->context);
			}

			$result = $service->call( $c, ($i == $length) ? $input : null );
		}

		return $result;
	}

	/**
	 * @param \Saltwater\Thing\Context $context
	 * @param string                   $cmd
	 * @param array                    $path
	 */
	protected function explode( $context, $cmd, $path )
	{
		$root = array_shift($path);

		// This is for simple commands upon an established service
		if ( empty($path) && !empty($this->chain) ) {
			$this->push($context, $cmd, $root);

			return;
		}

		$c = S::$n->context->get($root, $context);

		if ( $c ) {
			$context = $c;

			$root = array_shift($path);
		}

		$next = array_shift($path);

		// Either push a call on the last service or a new one into the chain
		if ( empty($path) ) {
			$this->push($context, $cmd, $root, $next);
		} else {
			$this->push($context, 'get', $root, $next);

			$this->explode($context, $cmd, $path);
		}
	}

	/**
	 * @param \Saltwater\Thing\Context $context
	 * @param string                   $cmd
	 * @param string                   $service
	 * @param string                   $path
	 */
	protected function push( $context, $cmd, $service, $path=null )
	{
		$method = $service;

		if ( !empty($path) && !is_numeric($path) ) {
			$method = $path;

			$path = null;
		}

		$this->chain[] = (object) array(
			'context'  => $context,
			'http'     => $cmd,
			'service'  => $service,
			'method'   => $method,
			'function' => $cmd . U::dashedToCamelCase($method),
			'path'     => $path
		);
	}

}

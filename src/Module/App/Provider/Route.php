<?php

namespace Saltwater\App\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\App\Common\Route as AbstractRoute;
use Saltwater\Salt\Service;
use Saltwater\Salt\Context;

class Route extends AbstractRoute
{
	protected function __construct()
	{
		$this->uri = $this->getURI();

		$this->http = $this->getHTTP();

		$context = S::$n->masterContext();

		if ( empty($context) ) return;

		$this->explode( $context, $this->http, explode('/', $this->uri) );
	}

	public static function getProvider()
	{
		$module = S::$n->getModule(self::$module);

		$class = U::className($module::getNamespace(), 'provider', 'route');

		return new $class;
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
		echo S::$n->response->response(
			$this->resolveChain( $this->getInput() )
		);
	}

	protected function getInput()
	{
		$input = @file_get_contents('php://input');

		return empty($input) ? null : json_decode($input);
	}

	protected function resolveChain( $input, $result=null )
	{
		$length = count($this->chain);

		$service = new Service;

		for ( $i=0; $i<$length; ++$i ) {
			$result = $this->chain(
				$service, $this->chain[$i], $input, $result, ($i == $length-1)
			);
		}

		return $result;
	}

	/**
	 * @param Service $service
	 * @param object  $item
	 * @param mixed   $input
	 * @param mixed   $result
	 * @param bool    $last
	 *
	 * @return mixed|null
	 */
	private function chain( &$service, &$item, $input, $result, $last )
	{
		$item->context->pushData($result);

		$service->setContext($item->context);

		if ( !$service->prepareCall($item) ) {
			$service = S::$n->service->get($item->service, $item->context);
		}

		return $service->call( $item, $last ? $input : null );
	}

	/**
	 * @param Context $context
	 * @param string  $cmd
	 * @param array   $path
	 */
	protected function explode( $context, $cmd, $path )
	{
		$root = array_shift($path);

		// This is for simple commands upon an established service
		if ( empty($path) && !empty($this->chain) ) {
			$this->push($context, $cmd, $root);

			return;
		}

		if ( $c = S::$n->context->get($root, $context) ) {
			$context = $c;

			$root = array_shift($path);
		}

		$this->explodePush($path, $context, $cmd, $root);
	}

	/**
	 * @param Context $context
	 * @param string  $cmd
	 */
	private function explodePush( $path, $context, $cmd, $root )
	{
		$next = array_shift($path);

		// Either push a call on the last service or a new one into the chain
		if ( empty($path) ) {
			$this->push($context, $cmd, $root, $next);
		} else {
			$this->push($context, 'get', $root, $next);

			// We have leftovers!
			$this->explode($context, $cmd, $path);
		}
	}

	/**
	 * @param Context $context
	 * @param string  $cmd
	 * @param string  $service
	 * @param string  $path
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

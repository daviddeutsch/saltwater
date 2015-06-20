<?php

namespace Saltwater\App\Provider;

use Saltwater\Server as S;
use Saltwater\Utils as U;
use Saltwater\App\Common\Route as AbstractRoute;

class Route extends AbstractRoute
{
	public function __construct()
	{
		$this->uri = $this->getURI();

		$this->http = $this->getHTTP();

		$context = S::$n->modules->finder->masterContext();

		if ( empty($context) ) return;

		$this->explode( $context, $this->http, explode('/', $this->uri) );
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

		// TODO: Temp measure, should be possible to achieve in routing (?)
		if ( strpos($path, '.zip') ) {
			$path = str_replace('.zip', '', $path);
		}

		return $path;
	}

	protected function getHTTP()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * @param Response $response
	 * @param ServiceChain $serviceChain
	 */
	public function go( $response, $serviceChain )
	{
		echo $response->response(
			$serviceChain->resolve( $this->getInput() )
		);
	}

	protected function getInput()
	{
		$input = @file_get_contents('php://input');

		return empty($input) ? null : json_decode($input);
	}

}

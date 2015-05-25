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

		$context = S::$n->modules->finder->masterContext();

		if ( empty($context) ) return;

		$this->explode( $context, $this->http, explode('/', $this->uri) );
	}

	public static function getProvider()
	{
		$module = S::$n->modules->get(self::$module);

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
		$chain = S::$n->service_chain->resolve( $this->getInput() );
		echo S::$n->response->response(
			S::$n->service_chain->resolve( $this->getInput() )
		);
	}

	protected function getInput()
	{
		$input = @file_get_contents('php://input');

		return empty($input) ? null : json_decode($input);
	}

}

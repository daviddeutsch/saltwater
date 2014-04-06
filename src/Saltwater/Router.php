<?php

namespace Saltwater;

use Saltwater\Server as S;

class Router
{
	public $http;

	public $uri;

	public $chain = array();

	public function __construct( $uri=null )
	{
		if ( empty($uri) ) {
			$this->uri = $this->getURI();
		} else {
			$this->uri = $uri;
		}

		$this->http = strtolower($_SERVER['REQUEST_METHOD']);

		$this->explode( S::$context[0], $this->http, explode('/', $this->uri) );
	}

	protected function getURI()
	{
		$path = $_SERVER['SCRIPT_NAME'];

		if ( strpos($_SERVER['REQUEST_URI'], $path) === false ) {
			$path = str_replace( '\\', '', dirname($path) );
		}

		$path = substr_replace( $_SERVER['REQUEST_URI'], '', 0, strlen($path) );

		if ( isset($_SERVER['QUERY_STRING']) ) {
			$path = str_replace('?' . $_SERVER['QUERY_STRING'], '', $path);
		}

		$path = preg_replace('`[^a-z0-9/._-]+`', '', strtolower($path));

		if ( strpos($path, '.zip') ) {
			$path = str_replace('.zip', '', $path);
		}

		return $path;
	}

	public function go()
	{
		$input = @file_get_contents('php://input');

		if ( !$input ) $input = '';

		$result = null;

		$length = count($this->chain) - 1;

		foreach ( $this->chain as $i => $call ) {
			$call->context->pushData($result);

			$service = $call->context->getService($call->class, $result);

			// TODO: Middleware for individual Services

			if ( ($i == $length) && !empty($input) ) {
				$result = $service->call($call, json_decode($input));
			} else {
				$result = $service->call($call);
			}
		}

		if ( is_object($result) || is_array($result) ) {
			S::returnJSON($result);
		} else {
			S::returnEcho($result);
		}
	}

	protected function explode( $context, $cmd, $path )
	{
		$root = array_shift($path);

		// This is for simple commands upon an established service
		if ( empty($path) ) {
			$this->push($context, $cmd, $root);

			return;
		}

		$c = S::findContext($root);

		if ( $c ) {
			// This is for switching into a child context
			$context = new $c($context);

			S::pushContext($context);

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

	protected function push( $context, $cmd, $service, $path=null )
	{
		$method = $service;

		if ( !empty($path) && !is_numeric($path) ) {
			$method = $path;

			$path = null;
		}

		$class = $context->findService($service);

		if ( strpos($method, '-') ) {
			$method = $cmd . str_replace(' ', '',
					ucwords( str_replace('-', ' ', $method) )
				);
		} else {
			$method = $cmd . ucfirst($method);
		}

		if ( !method_exists($class, $method) ) {
			$plain = true;
		} else {
			$plain = $method == $service;
		}

		$this->chain[] = (object) array(
			'context' => $context,
			'http' => $cmd,
			'service' => ucfirst($service),
			'class' => $class,
			'method' => $method,
			'plain' => $plain,
			'path' => $path
		);
	}
}

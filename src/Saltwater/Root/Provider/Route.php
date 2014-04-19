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

		$this->explode(
			S::$n->masterContext(),
			$this->http,
			explode('/', $this->uri),
			true
		);
	}

	public static function get()
	{
		$module = S::$n->getModule(self::$module);

		$class = $module->namespace . '\Provider\Route';

		return new $class();
	}

	protected function getURI()
	{
		$path = $_SERVER['SCRIPT_NAME'];

		$uri = $_SERVER['REQUEST_URI'];

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

		$result = null;

		$length = count($this->chain) - 1;

		foreach ( $this->chain as $i => $call ) {
			$call->context->pushData($result);

			$service = S::$n->service($call->class, $call->context);

			// TODO: Middleware for individual Services

			if ( ($i == $length) && !empty($input) ) {
				$result = $service->call($call, json_decode($input));
			} else {
				$result = $service->call($call);
			}
		}

		S::$n->response->response($result);
	}

	/**
	 * @param Context $context
	 * @param string  $cmd
	 * @param string  $path
	 */
	protected function explode( $context, $cmd, $path, $start=false )
	{
		$root = array_shift($path);

		// This is for simple commands upon an established service
		if ( empty($path) ) {
			$this->push($context, $cmd, $root);

			return;
		}

		$c = S::$n->context($root, $context);

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

		// TODO: Setting a class here and reusing it later is mad uglies
		$class = $context->namespace
		. '\Service\\'
		. U::dashedToCamelCase($service);

		if ( !class_exists($class) ) {
			$class = 'Saltwater\Root\Service\\'
				. U::dashedToCamelCase($service);

			if ( !class_exists($class) ) {
				$class = $context->namespace
					. '\Service\Rest';

				if ( !class_exists($class) ) {
					$class = 'Saltwater\Root\Service\Rest';
				}
			}
		}

		$method = $cmd . U::dashedToCamelCase($method);

		if ( !method_exists($class, $method) ) {
			$plain = true;
		} else {
			$plain = $method == $service;
		}

		$this->chain[] = (object) array(
			'context' => $context,
			'http' => $cmd,
			'service' => $service,
			'class' => $class,
			'method' => $method,
			'plain' => $plain,
			'path' => $path
		);
	}
}

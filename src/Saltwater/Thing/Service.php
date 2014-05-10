<?php

namespace Saltwater\Thing;

use Saltwater\Server as S;
use Saltwater\Utils as U;

/**
 * Services provide data or functionality
 */
class Service
{
	/**
	 * @var Context
	 */
	protected $context = null;

	/**
	 * @var string
	 */
	protected $module = null;

	public function __construct( $context=null, $module=null )
	{
		$this->setContext($context);

		$this->setModule($module);
	}

	public function setContext( $context )
	{
		$this->context = $context;
	}

	public function setModule( $module )
	{
		$this->module = $module;
	}

	public function is_callable( $method )
	{
		return method_exists($this, $method);
	}

	public function call( $call, $data=null )
	{
		$method = $call->http . U::dashedToCamelCase($call->method);

		if ( !$this->is_callable($this, $method) ) return null;

		return call_user_func_array(
			array($this, $call->method),
			$this->getMethodArgs($method, $call->path, $data)
		);
	}

	private function getMethodArgs( $method, $path, $data )
	{
		$reflect = new \ReflectionMethod($this, $method);

		if ( !$reflect->getNumberOfParameters() ) {
			return call_user_func( array($this, $method) );
		}

		$args = array();
		foreach ( $reflect->getParameters() as $parameter ) {
			$name = $parameter->getName();

			switch ( $name ) {
				case 'path':
					$args[] = $path;
					break;
				case 'data':
					$args[] = $data;
					break;
				default:
					$args[] = S::$n->provider($name, $this->module);
					break;
			}
		}

		return $args;
	}
}

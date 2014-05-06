<?php

namespace Saltwater\Thing;

use Saltwater\Server as S;

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
		$reflect = new \ReflectionMethod($this, $call->method);

		if ( !$reflect->getNumberOfParameters() ) {
			return call_user_func( array($this, $call->method) );
		}

		$args = array();
		foreach ( $reflect->getParameters() as $parameter ) {
			$name = $parameter->getName();

			switch ( $name ) {
				case 'path':
					$args[] = $call->path;
					break;
				case 'data':
					$args[] = $data;
					break;
				default:
					$args[] = S::$n->provider($name, $this->module);
					break;
			}
		}

		return call_user_func_array( array($this, $call->method), $args );
	}
}

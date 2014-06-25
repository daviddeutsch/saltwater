<?php

namespace Saltwater\Salt;

use Saltwater\Server as S;

/**
 * Services provide data or functionality
 */
class Service
{
	/** @var Context */
	protected $context = null;

	/** @var string */
	protected $module = null;

	public function __construct( $context=null, $module=null )
	{
		$this->setContext($context);

		if ( is_null($module) && !empty($context->module) ) {
			$module = $context->module->getName();
		}

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

	public function isCallable( $method )
	{
		return method_exists($this, $method);
	}

	public function prepareCall( $call )
	{
		return $this->isCallable($call->function);
	}

	/**
	 * Attempt to execute a call on this service
	 *
	 * @param            $call
	 * @param mixed|null $data
	 *
	 * @return mixed|null
	 */
	public function call( $call, $data=null )
	{
		if ( !$this->isCallable($call->function) ) return null;

		return $this->executeCall($call, $call->function, $data);
	}

	/**
	 * Execute a call
	 *
	 * @param object     $call
	 * @param string     $method
	 * @param mixed|null $data
	 *
	 * @return mixed
	 */
	protected function executeCall( $call, $method, $data )
	{
		$reflect = new \ReflectionMethod($this, $method);

		if ( !$reflect->getNumberOfParameters() ) {
			return call_user_func( array($this, $method) );
		}

		return call_user_func_array(
			array($this, $method),
			$this->getMethodArgs($reflect, $call->path, $data)
		);
	}

	/**
	 * @param \ReflectionMethod $reflect
	 * @param $path
	 * @param $data
	 *
	 * @return array
	 */
	private function getMethodArgs( $reflect, $path, $data )
	{
		$args = array();
		foreach ( $reflect->getParameters() as $parameter ) {
			$name = $parameter->getName();

			if ( $name == 'path' ) { $args[] = $path; continue; }

			if ( $name == 'data' ) { $args[] = $data; continue; }

			$args[] = S::$n->provider($name, $this->module);
		}

		return $args;
	}
}

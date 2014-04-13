<?php

namespace Saltwater\Thing;

class Service
{
	protected $context;

	public function __construct( $context )
	{
		$this->context = $context;
	}

	public function is_callable( $method )
	{
		return method_exists($this, $method);
	}

	public function call( $call, $data=null )
	{
		$func = array($this, $call->method);

		if ( empty( $call->path ) && empty( $data ) ) {
			return call_user_func($func);
		}

		if ( empty( $call->path ) ) {
			return call_user_func($func, $data);
		} else {
			return call_user_func($func, $call->path, $data);
		}
	}
}

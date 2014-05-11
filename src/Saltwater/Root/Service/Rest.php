<?php

namespace Saltwater\Root\Service;

use Saltwater\Server as S;
use Saltwater\Thing\Service;

class Rest extends Service
{
	public function is_callable( $method )
	{
		return strpos(
			$method,
			array_pop( explode('\\', get_class($this) ) )
		) !== false;
	}

	public function call( $call, $data=null )
	{
		if ( parent::is_callable($call->function) ) {
			return parent::call($call, $data);
		}

		return $this->restCall($call, $data);
	}

	protected function restCall( $call, $data=null )
	{
		$path = $call->method;

		if ( is_numeric($call->path) ) {
			$path .= '/' . $call->path;
		}

		return $this->callPath($call->http, $path, $data);
	}

	/**
	 * @param string $path
	 */
	protected function callPath( $http, $path, $data=null )
	{
		$rest = $this->restHandler();

		return $rest->handleRESTRequest($http, $path, $data);
	}

	protected function restHandler()
	{
		return new \RedBean_Plugin_BeanCan(S::$n->db);
	}
}
